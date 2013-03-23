<?php

namespace YsTools\BackUrlBundle\EventListener;

use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Doctrine\Common\Annotations\Reader;
use YsTools\BackUrlBundle\Annotation\AnnotationInterface;

class ControllerListener
{
    /**
     * @var Reader
     */
    protected $reader;

    /**
     * @var string
     */
    protected $storageName;

    /**
     * @param Reader $reader
     * @param string $storageName
     */
    public function __construct(Reader $reader, $storageName)
    {
        $this->reader      = $reader;
        $this->storageName = $storageName;
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

        // set annotation to request
        if ($annotations) {
            $request = $event->getRequest();

            if (!$request->attributes->has($this->storageName)) {
                $request->attributes->set($this->storageName, array());
            }
            $annotationStorage = $request->attributes->get($this->storageName);

            /** @var $annotation AnnotationInterface */
            foreach ($annotations as $annotation) {
                $annotationStorage[$annotation->getCode()] = $annotation;
            }

            $request->attributes->set($this->storageName, $annotationStorage);
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
