<?php

namespace YsTools\BackUrlBundle\Annotation;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Back URL annotation configuration container
 *
 * @Annotation
 */
class BackUrl implements AnnotationInterface
{
    /**
     * Type code
     */
    const TYPE_CODE = '_back_url';

    /**
     * URL parameter name
     *
     * @var string
     */
    protected $parameterName = 'backUrl';

    /**
     * @param array $values
     */
    public function __construct(array $values = array())
    {
        if (isset($values['parameter'])) {
            $this->parameterName = $values['parameter'];
        } elseif (isset($values['value'])) {
            $this->parameterName = $values['value'];
        }
    }

    /**
     * @return string
     */
    public function getParameterName()
    {
        return $this->parameterName;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return self::TYPE_CODE;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function process(Request $request, Response $response)
    {
        if (!$response->isSuccessful()) {
            return $response;
        }

        $backUrl = $request->get($this->parameterName);
        if (!$backUrl) {
            return $response;
        }

        return new RedirectResponse($backUrl);
    }
}
