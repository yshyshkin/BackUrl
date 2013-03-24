<?php

namespace YsTools\BackUrlBundle\EventListener;

use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Doctrine\Common\Annotations\Reader;
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

        // get controller and method
        $object = new \ReflectionObject($controller[0]);
        $method = $object->getMethod($controller[1]);

        // get both class and method annotations
        $classAnnotations  = $this->getAnnotations($this->reader->getClassAnnotations($object));
        $methodAnnotations = $this->getAnnotations($this->reader->getMethodAnnotations($method));
        $annotations       = array_merge($classAnnotations, $methodAnnotations);

        // save annotations in storage
        /** @var $annotation AnnotationInterface */
        foreach ($annotations as $annotation) {
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
