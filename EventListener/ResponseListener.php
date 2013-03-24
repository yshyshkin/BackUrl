<?php

namespace YsTools\BackUrlBundle\EventListener;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use YsTools\BackUrlBundle\Annotation\AnnotationInterface;
use YsTools\BackUrlBundle\Annotation\StorageInterface;

class ResponseListener
{
    /**
     * @var string
     */
    protected $storage;

    /**
     * @param StorageInterface $storage
     */
    public function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        /** @var $annotation AnnotationInterface */
        foreach ($this->storage->getAnnotations() as $annotation) {
            $response = $annotation->process($event->getRequest(), $event->getResponse());
            $event->setResponse($response);
        }

        $this->storage->clearAnnotations();
    }
}
