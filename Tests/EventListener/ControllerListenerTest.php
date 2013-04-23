<?php

namespace YsTools\BackUrlBundle\Tests\EventListener;

use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Annotations\Reader;
use YsTools\BackUrlBundle\Annotation\Storage;
use YsTools\BackUrlBundle\EventListener\ControllerListener;
use YsTools\BackUrlBundle\Annotation\AnnotationInterface;

class ControllerListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test parameters
     */
    const CODE_ONE = 'code_one';
    const CODE_TWO = 'code_two';

    public function testOnKernelControllerNoController()
    {
        $reader = $this->getMockForAbstractClass(
            'Doctrine\Common\Annotations\Reader',
            array(),
            '',
            false,
            true,
            true,
            array('getClassAnnotations', 'getMethodAnnotations')
        );
        $reader->expects($this->never())->method('getClassAnnotations');
        $reader->expects($this->never())->method('getMethodAnnotations');

        $storage = new Storage();

        $event = $this->getMock(
            'Symfony\Component\HttpKernel\Event\FilterControllerEvent',
            array('getController'),
            array(),
            '',
            false
        );
        $event->expects($this->once())
            ->method('getController')
            ->will($this->returnValue(null)); // any not array value

        $controllerListener = new ControllerListener($reader, $storage);
        $controllerListener->onKernelController($event);

        $this->assertEmpty($storage->getAnnotations());
    }

    /**
     * Generate annotation object with specified code
     *
     * @param string $code
     * @return AnnotationInterface
     */
    protected function generateAnnotation($code)
    {
        $annotation = $this->getMockForAbstractClass(
            'YsTools\BackUrlBundle\Annotation\AnnotationInterface',
            array(),
            '',
            false,
            true,
            true,
            array('getCode', 'initialize')
        );
        $annotation->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue($code));
        $annotation->expects($this->once())
            ->method('initialize')
            ->with($this->isInstanceOf('Symfony\Component\HttpFoundation\Request'));

        return $annotation;
    }

    /**
     * Data provider for testOnKernelController
     *
     * @return array
     */
    public function onKernelControllerDataProvider()
    {
        $controllerCodeOne = $this->generateAnnotation(self::CODE_ONE);
        $methodCodeOne     = $this->generateAnnotation(self::CODE_ONE);

        $controllerBothCodeOne = $this->generateAnnotation(self::CODE_ONE);
        $controllerBothCodeTwo = $this->generateAnnotation(self::CODE_TWO);
        $methodBothCodeOne     = $this->generateAnnotation(self::CODE_ONE);

        return array(
            'no annotations' => array(
                '$controllerAnnotations' => array(),
                '$methodAnnotations'     => array(),
                '$expectedAnnotations'   => array(),
            ),
            'controller annotations' => array(
                '$controllerAnnotations' => array($controllerCodeOne),
                '$methodAnnotations'     => array(),
                '$expectedAnnotations'   => array(
                    self::CODE_ONE => $controllerCodeOne
                ),
            ),
            'method annotations' => array(
                '$controllerAnnotations' => array(),
                '$methodAnnotations'     => array($methodCodeOne),
                '$expectedAnnotations'   => array(
                    self::CODE_ONE => $methodCodeOne
                ),
            ),
            'both annotations' => array(
                '$controllerAnnotations' => array($controllerBothCodeOne, $controllerBothCodeTwo),
                '$methodAnnotations'     => array($methodBothCodeOne),
                '$expectedAnnotations'   => array( // merged data
                    self::CODE_TWO => $controllerBothCodeTwo,
                    self::CODE_ONE => $methodBothCodeOne
                ),
            ),
        );
    }

    /**
     * @param array $controllerAnnotations
     * @param array $methodAnnotations
     * @param array $expectedAnnotations
     * @dataProvider onKernelControllerDataProvider
     */
    public function testOnKernelController(
        array $controllerAnnotations,
        array $methodAnnotations,
        array $expectedAnnotations
    ) {
        // fake controller - just to be sure that controller was processed
        $controller = new \DateTime();
        $method     = 'getTimestamp';

        $reader = $this->getMockForAbstractClass(
            'Doctrine\Common\Annotations\Reader',
            array(),
            '',
            false,
            true,
            true,
            array('getClassAnnotations', 'getMethodAnnotations')
        );
        $reader->expects($this->once())
            ->method('getClassAnnotations')
            ->with($this->isInstanceOf('\ReflectionClass'))
            ->will($this->returnValue($controllerAnnotations));
        $reader->expects($this->once())
            ->method('getMethodAnnotations')
            ->with($this->isInstanceOf('\ReflectionMethod'))
            ->will($this->returnValue($methodAnnotations));

        $storage = new Storage();

        $request = new Request();

        $event = $this->getMock(
            'Symfony\Component\HttpKernel\Event\FilterControllerEvent',
            array('getController', 'getRequest'),
            array(),
            '',
            false
        );
        $event->expects($this->once())
            ->method('getController')
            ->will($this->returnValue(array($controller, $method))); // fake controller
        $event->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($request));

        $controllerListener = new ControllerListener($reader, $storage);
        $controllerListener->onKernelController($event);

        $this->assertEquals($expectedAnnotations, $storage->getAnnotations());
    }
}
