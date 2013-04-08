<?php

namespace YsTools\BackUrlBundle\Tests\Annotation;

use YsTools\BackUrlBundle\Annotation\BackUrl;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;

class BackUrlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test parameters
     */
    const TEST_PARAMETER = 'test_parameter';
    const TEST_VALUE     = 'test_value';
    const TEST_URL       = 'http://test.url/';

    /**
     * Data provider for testGetParameterName
     *
     * @return array
     */
    public function getParameterNameDataProvider()
    {
        return array(
            'empty' => array(
                '$source'   => array(),
                '$expected' => 'backUrl',
            ),
            'value element' => array(
                '$source'   => array(
                    'value' => self::TEST_VALUE,
                ),
                '$expected' => self::TEST_VALUE,
            ),
            'parameter element' => array(
                '$source'   => array(
                    'value'     => self::TEST_VALUE,
                    'parameter' => self::TEST_PARAMETER,
                ),
                '$expected' => self::TEST_PARAMETER,
            ),
        );
    }

    public function testGetCode()
    {
        $annotation = new BackUrl();
        $this->assertEquals(BackUrl::TYPE_CODE, $annotation->getCode());
    }

    public function processDataProvider()
    {
        return array(
            'not successful' => array(
                '$backUrlParameters' => array(),
                '$requestParameters' => array(self::TEST_PARAMETER => self::TEST_URL),
                '$sessionFlashBag'   => array(),
                '$responseCode'      => 404
            ),
            'not successful force' => array(
                '$backUrlParameters' => array('force' => true),
                '$requestParameters' => array(),
                '$sessionFlashBag'   => array(),
                '$responseCode'      => 200
            ),
            'simple no parameter' => array(
                '$backUrlParameters' => array(),
                '$requestParameters' => array(),
                '$sessionFlashBag'   => array(),
                '$responseCode'      => 200
            ),
            'simple empty parameter' => array(
                '$backUrlParameters' => array('parameter' => self::TEST_PARAMETER),
                '$requestParameters' => array(self::TEST_PARAMETER => ''),
                '$sessionFlashBag'   => array(),
                '$responseCode'      => 200
            ),
            'simple redirect' => array(
                '$backUrlParameters' => array('parameter' => self::TEST_PARAMETER),
                '$requestParameters' => array(self::TEST_PARAMETER => self::TEST_URL),
                '$sessionFlashBag'   => array(),
                '$responseCode'      => 200,
                '$isRedirect'        => true,
            ),
            'flash bag no redirect' => array(
                '$backUrlParameters' => array('parameter' => self::TEST_PARAMETER, 'useFlashBag' => true),
                '$requestParameters' => array(self::TEST_PARAMETER => ''),
                '$sessionFlashBag'   => array(self::TEST_PARAMETER => array(self::TEST_URL)),
                '$responseCode'      => 200
            ),
            'flash bag redirect' => array(
                '$backUrlParameters' => array('parameter' => self::TEST_PARAMETER, 'useFlashBag' => true),
                '$requestParameters' => array(self::TEST_PARAMETER => self::TEST_URL),
                '$sessionFlashBag'   => array(),
                '$responseCode'      => 200,
                '$isRedirect'        => true,
            ),
        );
    }

    /**
     * @param array $backUrlParameters
     * @param array $requestParameters
     * @param array $sessionFlashBag
     * @param int $responseCode
     * @param bool $isRedirect
     * @dataProvider processDataProvider
     */
    public function testProcess(
        array $backUrlParameters,
        array $requestParameters,
        array $sessionFlashBag,
        $responseCode,
        $isRedirect = false
    ) {
        $annotation = new BackUrl($backUrlParameters);
        $request    = new Request($requestParameters);
        $response   = new Response('', $responseCode);

        // init fake session
        $flashBag = new FlashBag();
        $session = $this->getMock(
            'Symfony\Component\HttpFoundation\Session\Session',
            array('getFlashBag', 'start'),
            array(),
            '',
            false
        );
        $session->expects($this->any())
            ->method('getFlashBag')
            ->will($this->returnValue($flashBag));
        $request->setSession($session);

        $annotation->initialize($request);
        $flashBag->setAll($sessionFlashBag);

        if ($isRedirect) {
            /** @var $redirectResponse RedirectResponse */
            $redirectResponse = $annotation->process($request, $response);
            $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $redirectResponse);
            $this->assertEquals(self::TEST_URL, $redirectResponse->getTargetUrl());
        } else {
            $this->assertEquals($response, $annotation->process($request, $response));
        }
    }

    /**
     * Data provider for testInitialize
     *
     * @return array
     */
    public function initializeDataProvider()
    {
        return array(
            'no data' => array(
                '$backUrlParameters' => array('useFlashBag' => true),
                '$requestParameters' => array(),
                '$sessionFlashBag'   => array(),
                '$expected' => array(
                    'flashBag' => array(),
                    'flashBagUrl' => null
                )
            ),
            'set flash bag url' => array(
                '$backUrlParameters' => array('parameter' => self::TEST_PARAMETER, 'useFlashBag' => true),
                '$requestParameters' => array(self::TEST_PARAMETER => self::TEST_URL),
                '$sessionFlashBag'   => array(),
                '$expected' => array(
                    'flashBag' => array(self::TEST_PARAMETER => array(self::TEST_URL)),
                    'flashBagUrl' => self::TEST_URL
                )
            ),
            'store flash bag url' => array(
                '$backUrlParameters' => array('parameter' => self::TEST_PARAMETER, 'useFlashBag' => true),
                '$requestParameters' => array(),
                '$sessionFlashBag'   => array(self::TEST_PARAMETER => array(self::TEST_URL)),
                '$expected' => array(
                    'flashBag' => array(self::TEST_PARAMETER => array(self::TEST_URL)),
                    'flashBagUrl' => self::TEST_URL
                )
            ),
        );
    }

    /**
     * @param array $backUrlParameters
     * @param array $requestParameters
     * @param array $sessionFlashBag
     * @param array $expected
     * @dataProvider initializeDataProvider
     */
    public function testInitialize(
        array $backUrlParameters,
        array $requestParameters,
        array $sessionFlashBag,
        array $expected
    ) {
        $annotation = new BackUrl($backUrlParameters);
        $request    = new Request($requestParameters);

        // init fake session
        $flashBag = new FlashBag();
        $flashBag->setAll($sessionFlashBag);
        $session = $this->getMock(
            'Symfony\Component\HttpFoundation\Session\Session',
            array('getFlashBag', 'start'),
            array(),
            '',
            false
        );
        $session->expects($this->any())
            ->method('getFlashBag')
            ->will($this->returnValue($flashBag));
        $request->setSession($session);

        $annotation->initialize($request);

        $this->assertEquals($expected['flashBag'], $flashBag->peekAll());
        $this->assertAttributeEquals($expected['flashBagUrl'], 'flashBagUrl', $annotation);
    }
}
