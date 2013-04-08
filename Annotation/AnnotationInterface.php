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
     * Initialize annotation before controller action
     *
     * @abstract
     * @param Request $request
     */
    public function initialize(Request $request);

    /**
     * Process annotation after controller action
     *
     * @abstract
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function process(Request $request, Response $response);
}
