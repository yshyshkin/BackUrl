<?php

namespace YsTools\BackUrlBundle\Annotation;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;

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
     * Flag of force response replacing
     *
     * @var bool
     */
    protected $force = false;

    /**
     * Flash bag usage flag
     *
     * @var bool
     */
    protected $useFlashBag = false;

    /**
     * Flash bag URL
     *
     * @var string
     */
    protected $flashBagUrl;

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

        if (isset($values['force']) && $values['force']) {
            $this->force = true;
        }

        if (isset($values['useFlashBag']) && $values['useFlashBag']) {
            $this->useFlashBag = true;
        }
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
     * @return void
     */
    public function initialize(Request $request)
    {
        if ($this->useFlashBag) {
            /** @var $session Session */
            $session = $request->getSession();
            $session->start();

            $backUrl = $request->get($this->parameterName);

            // add URL to flash bag
            if (!$session->getFlashBag()->has($this->parameterName) && $backUrl) {
                $session->getFlashBag()->set($this->parameterName, $backUrl);
            }

            // save flash bag URL
            if ($session->getFlashBag()->has($this->parameterName)) {
                $urls = $session->getFlashBag()->peek($this->parameterName);
                $this->flashBagUrl = reset($urls);
            }
        }
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function process(Request $request, Response $response)
    {
        if (!$response->isSuccessful() && !$this->force) {
            return $response;
        }

        if ($this->useFlashBag) {
            return $this->processFlashBagRedirect($request, $response);
        } else {
            return $this->processSimpleRedirect($request, $response);
        }
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    protected function processFlashBagRedirect(Request $request, Response $response)
    {
        /** @var $session Session */
        $session = $request->getSession();

        // if flash bag was cleared
        if ($this->flashBagUrl && !$session->getFlashBag()->has($this->parameterName)) {
            return $this->getRedirectResponse($this->flashBagUrl);
        }

        return $response;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    protected function processSimpleRedirect(Request $request, Response $response)
    {
        $backUrl = $request->get($this->parameterName);
        if (!$backUrl) {
            return $response;
        }

        return $this->getRedirectResponse($backUrl);
    }

    /**
     * @param string $backUrl
     * @return RedirectResponse
     */
    protected function getRedirectResponse($backUrl)
    {
        return new RedirectResponse($backUrl);
    }
}
