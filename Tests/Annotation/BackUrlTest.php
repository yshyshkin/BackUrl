<?php

namespace YsTools\BackUrlBundle\Tests\Annotation;

use YsTools\BackUrlBundle\Annotation\BackUrl;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

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

    /**
     * @param array $source
     * @param string $expected
     * @dataProvider getParameterNameDataProvider
     */
    public function testGetParameterName(array $source, $expected)
    {
        $annotation = new BackUrl($source);
        $this->assertAttributeEquals($expected, 'parameterName', $annotation);
        $this->assertEquals($expected, $annotation->getParameterName());
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
                '$parameters'   => array(self::TEST_PARAMETER => self::TEST_URL),
                '$responseCode' => 404
            ),
            'no parameter' => array(
                '$parameters'   => array(),
                '$responseCode' => 200
            ),
            'empty parameter' => array(
                '$parameters'   => array(self::TEST_PARAMETER => ''),
                '$responseCode' => 200
            ),
            'redirect' => array(
                '$parameters'   => array(self::TEST_PARAMETER => self::TEST_URL),
                '$responseCode' => 200,
                '$isRedirect'   => true,
            ),
        );
    }

    /**
     * @param array $parameters
     * @param int $responseCode
     * @param bool $isRedirect
     * @dataProvider processDataProvider
     */
    public function testProcess(array $parameters, $responseCode, $isRedirect = false)
    {
        $annotation = new BackUrl(array('parameter' => self::TEST_PARAMETER));
        $request    = new Request($parameters);
        $response   = new Response('', $responseCode);

        if ($isRedirect) {
            /** @var $redirectResponse RedirectResponse */
            $redirectResponse = $annotation->process($request, $response);
            $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $redirectResponse);
            $this->assertEquals(self::TEST_URL, $redirectResponse->getTargetUrl());
        } else {
            $this->assertEquals($response, $annotation->process($request, $response));
        }
    }
}
