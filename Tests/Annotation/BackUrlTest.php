<?php

namespace YsTools\BackUrlBundle\Tests\Annotation;

use YsTools\BackUrlBundle\Annotation\BackUrl;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;

class BackUrlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test parameters
     */
    const TEST_PARAMETER = 'test_parameter';
    const TEST_VALUE     = 'test_value';
    const TEST_URL       = 'http://test.url/';

    public function testGetCode()
    {
        $annotation = new BackUrl();
        $this->assertEquals(BackUrl::TYPE_CODE, $annotation->getCode());
    }

    /**
     * @param array $sessionParameters
     * @return Session
     */
    protected function getSession(array $sessionParameters)
    {
        $sessionStorage = $this->getMock(
            'Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage',
            array('start')
        );
        $session = new Session($sessionStorage);
        foreach ($sessionParameters as $name => $value) {
            $session->set($name, $value);
        }

        return $session;
    }

    /**
     * Data provider for testProcess
     *
     * @return array
     */
    public function processDataProvider()
    {
        return array(
            'empty' => array(
                '$expected' => array(
                    'responseCode' => 200,
                    'session'      => array(),
                ),
            ),
            'no data' => array(
                '$expected' => array(
                    'responseCode' => 200,
                    'session'      => array(),
                ),
                '$triggerRedirect' => true,
                '$isRedirect'      => false,
            ),
            'not triggered' => array(
                '$expected' => array(
                    'responseCode' => 200,
                    'session'      => array(),
                ),
                '$triggerRedirect'   => false,
                '$isRedirect'        => false,
                '$backUrlParameters' => array(),
                '$requestParameters' => array(self::TEST_PARAMETER => self::TEST_URL),
            ),
            'redirect' => array(
                '$expected' => array(
                    'responseCode' => 302,
                    'session'      => array(),
                ),
                '$triggerRedirect'   => true,
                '$isRedirect'        => true,
                '$backUrlParameters' => array('parameter' => self::TEST_PARAMETER),
                '$requestParameters' => array(self::TEST_PARAMETER => self::TEST_URL),
            ),
            'init url in session' => array(
                '$expected' => array(
                    'responseCode' => 200,
                    'session'      => array(self::TEST_PARAMETER => self::TEST_URL),
                ),
                '$triggerRedirect'   => false,
                '$isRedirect'        => false,
                '$backUrlParameters' => array('parameter' => self::TEST_PARAMETER, 'useSession' => true),
                '$requestParameters' => array(self::TEST_PARAMETER => self::TEST_URL),
                '$sessionParameters' => array(),
            ),
            'save url in session' => array(
                '$expected' => array(
                    'responseCode' => 200,
                    'session'      => array(self::TEST_PARAMETER => self::TEST_URL),
                ),
                '$triggerRedirect'   => false,
                '$isRedirect'        => false,
                '$backUrlParameters' => array('parameter' => self::TEST_PARAMETER, 'useSession' => true),
                '$requestParameters' => array(),
                '$sessionParameters' => array(self::TEST_PARAMETER => self::TEST_URL),
            ),
            'redirect using session' => array(
                '$expected' => array(
                    'responseCode' => 302,
                    'session'      => array(),
                ),
                '$triggerRedirect'   => true,
                '$isRedirect'        => true,
                '$backUrlParameters' => array('parameter' => self::TEST_PARAMETER, 'useSession' => true),
                '$requestParameters' => array(),
                '$sessionParameters' => array(self::TEST_PARAMETER => self::TEST_URL),
            ),
        );
    }

    /**
     * @param array $expected
     * @param bool $triggerRedirect
     * @param bool $isRedirect
     * @param array $backUrlParameters
     * @param array $requestParameters
     * @param array $sessionParameters
     * @dataProvider processDataProvider
     */
    public function testProcess(
        array $expected,
        $triggerRedirect = false,
        $isRedirect = false,
        array $backUrlParameters = array(),
        array $requestParameters = array(),
        array $sessionParameters = array()
    ) {
        $annotation = new BackUrl($backUrlParameters);
        BackUrl::triggerRedirect(false);
        if ($triggerRedirect) {
            BackUrl::triggerRedirect();
        }

        $session = $this->getSession($sessionParameters);
        $request = new Request($requestParameters);
        $request->setSession($session);
        $response = new Response('');

        $annotation->initialize($request);
        $actualResponse = $annotation->process($request, $response);

        $this->assertEquals($expected['responseCode'], $actualResponse->getStatusCode());
        foreach ($expected['session'] as $name => $expectedValue) {
            $this->assertEquals($expectedValue, $session->get($name));
        }

        if ($isRedirect) {
            /** @var $actualResponse RedirectResponse */
            $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $actualResponse);
            $this->assertEquals(self::TEST_URL, $actualResponse->getTargetUrl());
        } else {
            $this->assertEquals($response, $actualResponse);
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
                '$expected' => array(
                    'backUrl' => null,
                    'session' => array(),
                )
            ),
            'set regular url' => array(
                '$expected' => array(
                    'backUrl' => self::TEST_URL,
                    'session' => array(),
                ),
                '$backUrlParameters' => array('parameter' => self::TEST_PARAMETER),
                '$requestParameters' => array(self::TEST_PARAMETER => self::TEST_URL),
            ),
            'set session url' => array(
                '$expected' => array(
                    'backUrl' => self::TEST_URL,
                    'session' => array(self::TEST_PARAMETER => self::TEST_URL)
                ),
                '$backUrlParameters' => array('parameter' => self::TEST_PARAMETER, 'useSession' => true),
                '$requestParameters' => array(self::TEST_PARAMETER => self::TEST_URL),
            ),
            'save session url' => array(
                '$expected' => array(
                    'backUrl' => self::TEST_URL,
                    'session' => array(self::TEST_PARAMETER => self::TEST_URL),
                ),
                '$backUrlParameters' => array('parameter' => self::TEST_PARAMETER, 'useSession' => true),
                '$requestParameters' => array(),
                '$sessionParameters' => array(self::TEST_PARAMETER => self::TEST_URL),
            ),
        );
    }

    /**
     * @param array $expected
     * @param array $backUrlParameters
     * @param array $requestParameters
     * @param array $sessionParameters
     * @dataProvider initializeDataProvider
     */
    public function testInitialize(
        array $expected,
        array $backUrlParameters = array(),
        array $requestParameters = array(),
        array $sessionParameters = array()
    ) {
        $session = $this->getSession($sessionParameters);
        $request = new Request($requestParameters);
        $request->setSession($session);

        $annotation = new BackUrl($backUrlParameters);
        $annotation->initialize($request);

        $this->assertAttributeEquals($expected['backUrl'], 'backUrl', $annotation);
        foreach ($expected['session'] as $name => $expectedValue) {
            $this->assertEquals($expectedValue, $session->get($name));
        }
    }
}
