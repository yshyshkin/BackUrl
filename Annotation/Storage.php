<?php

namespace YsTools\BackUrlBundle\Annotation;

/**
 * Annotation storage
 */
class Storage implements StorageInterface
{
    /**
     * @var array
     */
    protected $annotations = array();

    /**
     * Get list of all annotations
     *
     * @return array
     */
    public function getAnnotations()
    {
        return $this->annotations;
    }

    /**
     * Add new annotation
     *
     * @param AnnotationInterface $annotation
     * @return void
     */
    public function addAnnotation(AnnotationInterface $annotation)
    {
        $this->annotations[$annotation->getCode()] = $annotation;
    }

    /**
     * Clear all annotations
     *
     * @return void
     */
    public function clearAnnotations()
    {
        $this->annotations = array();
    }

}
