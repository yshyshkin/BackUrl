<?php

namespace YsTools\BackUrlBundle\Annotation;

/**
 * Annotation storage interface
 */
interface StorageInterface
{
    /**
     * Get list of all annotations
     *
     * @abstract
     * @return array
     */
    public function getAnnotations();

    /**
     * Add new annotation
     *
     * @abstract
     * @param AnnotationInterface $annotation
     * @return void
     */
    public function addAnnotation(AnnotationInterface $annotation);

    /**
     * Clear all annotations
     *
     * @abstract
     * @return void
     */
    public function clearAnnotations();
}
