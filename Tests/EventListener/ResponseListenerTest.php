<?php

namespace YsTools\BackUrlBundle\Tests\EventListener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use YsTools\BackUrlBundle\EventListener\ResponseListener;
use YsTools\BackUrlBundle\Annotation\AnnotationInterface;
use YsTools\BackUrlBundle\Annotation\Storage;

class ResponseListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test parameter
     */
    const NUMBER_OF_ANNOTATIONS = 5;

    /**
     * Generate annotation with predefined behaviour
     *
     * @param Request $request
     * @param Response $response
     * @param Response $modifiedResponse
     * @return AnnotationInterface
     */
    protected function generateAnnotation(Request $request, Response $response, Response $modifiedResponse)
    {
        $annotation = $this->getMockForAbstractClass(
            'YsTools\BackUrlBundle\Annotation\AnnotationInterface',
            array(),
            '',
            false,
            true,
            true,
            array('getCode', 'process')
        );
        $annotation->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue(uniqid()));
        $annotation->expects($this->any())
            ->method('process')
            ->with($request, $response)
            ->will($this->returnValue($modifiedResponse));

        return $annotation;
    }

    public function testOnKernelResponse()
    {
        $request          = new Request();
        $response         = new Response;
        $modifiedResponse = new RedirectResponse('http://test.url/');

        $storage = new Storage();
        for ($i = 0; $i < self::NUMBER_OF_ANNOTATIONS; $i++) {
            $annotation = $this->generateAnnotation($request, $response, $modifiedResponse);
            $storage->addAnnotation($annotation);
        }

        $event = $this->getMock(
            'Symfony\Component\HttpKernel\Event\FilterResponseEvent',
            array('getRequest', 'getResponse', 'setResponse'),
            array(),
            '',
            false
        );
        $event->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($request));
        $event->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue($response));
        $event->expects($this->exactly(self::NUMBER_OF_ANNOTATIONS))
            ->method('setResponse')
            ->with($modifiedResponse);

        $responseListener = new ResponseListener($storage);
        $responseListener->onKernelResponse($event);

        $this->assertEmpty($storage->getAnnotations());
    }
}
