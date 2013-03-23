<?php

namespace YsTools\BackUrlBundle\Annotation;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Back URL annotation interface
 */
interface AnnotationInterface
{
    /**
     * Annotation type code
     *
     * @abstract
     * @return string
     */
    public function getCode();

    /**
     * Process annotation
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function process(Request $request, Response $response);
}
