<?php

namespace YsTools\BackUrlBundle\EventListener;

use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Util\ClassUtils;
use YsTools\BackUrlBundle\Annotation\AnnotationInterface;
use YsTools\BackUrlBundle\Annotation\StorageInterface;

class ControllerListener
{
    /**
     * @var Reader
     */
    protected $reader;

    /**
     * @var StorageInterface
     */
    protected $storage;

    /**
     * @param Reader $reader
     * @param StorageInterface $storage
     */
    public function __construct(Reader $reader, StorageInterface $storage)
    {
        $this->reader  = $reader;
        $this->storage = $storage;
    }

    /**
     * @param FilterControllerEvent $event
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        if (!is_array($controller = $event->getController())) {
            return;
        }

        // get controller class name
        $className = class_exists('Doctrine\Common\Util\ClassUtils')
            ? ClassUtils::getClass($controller[0])
            : get_class($controller[0]);

        // get controller and method
        $object = new \ReflectionClass($className);
        $method = $object->getMethod($controller[1]);

        // get both class and method annotations
        $classAnnotations  = $this->getAnnotations($this->reader->getClassAnnotations($object));
        $methodAnnotations = $this->getAnnotations($this->reader->getMethodAnnotations($method));
        $annotations       = array_merge($classAnnotations, $methodAnnotations);

        // save annotations in storage
        /** @var $annotation AnnotationInterface */
        foreach ($annotations as $annotation) {
            $annotation->initialize($event->getRequest());
            $this->storage->addAnnotation($annotation);
        }
    }

    /**
     * @param array $annotations
     * @return array
     */
    protected function getAnnotations(array $annotations)
    {
        $result = array();
        /** @var $annotation AnnotationInterface */
        foreach ($annotations as $annotation) {
            if ($annotation instanceof AnnotationInterface) {
                $result[$annotation->getCode()] = $annotation;
            }
        }

        return $result;
    }
}
