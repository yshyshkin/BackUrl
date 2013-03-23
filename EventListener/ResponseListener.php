<?php

namespace YsTools\BackUrlBundle\EventListener;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use YsTools\BackUrlBundle\Annotation\AnnotationInterface;

class ResponseListener
{
    /**
     * @var string
     */
    protected $storageName;

    /**
     * @param string $storageName
     */
    public function __construct($storageName)
    {
        $this->storageName = $storageName;
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $request = $event->getRequest();
        if (!$request->attributes->has($this->storageName)) {
            return;
        }

        $annotationStorage = $request->attributes->get($this->storageName);
        /** @var $annotation AnnotationInterface */
        foreach ($annotationStorage as $annotation) {
            if ($annotation instanceof AnnotationInterface) {
                $response = $annotation->process($request, $event->getResponse());
                $event->setResponse($response);
            }
        }
        $request->attributes->remove($this->storageName);
    }
}
