<?php

namespace YsTools\BackUrlBundle\Tests\Annotation;

use YsTools\BackUrlBundle\Annotation\Storage;
use YsTools\BackUrlBundle\Annotation\BackUrl;
use YsTools\BackUrlBundle\Annotation\AnnotationInterface;

class StorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Storage
     */
    protected $storage;

    protected function setUp()
    {
        $this->storage = new Storage();
    }

    protected function tearDown()
    {
        unset($this->storage);
    }

    /**
     * Test annotation instance
     *
     * @return AnnotationInterface
     */
    protected function getTestAnnotation()
    {
        return new BackUrl();
    }

    public function testAddAnnotation()
    {
        $testAnnotation = $this->getTestAnnotation();
        $this->assertAttributeEmpty('annotations', $this->storage);
        $this->storage->addAnnotation($testAnnotation);
        $this->assertAttributeEquals(
            array($testAnnotation->getCode() => $testAnnotation),
            'annotations',
            $this->storage
        );
    }

    public function testGetAnnotations()
    {
        $testAnnotation = $this->getTestAnnotation();
        $this->storage->addAnnotation($testAnnotation);
        $this->assertEquals(
            array($testAnnotation->getCode() => $testAnnotation),
            $this->storage->getAnnotations()
        );
    }

    public function testClearAnnotations()
    {
        $testAnnotation = $this->getTestAnnotation();
        $this->storage->addAnnotation($testAnnotation);
        $this->assertAttributeNotEmpty('annotations', $this->storage);
        $this->storage->clearAnnotations();
        $this->assertAttributeEmpty('annotations', $this->storage);
    }
}
