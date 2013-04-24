<?php

namespace YsTools\BackUrlBundle\Annotation;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

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
     * Flash bag usage flag
     *
     * @var bool
     */
    protected $useSession = false;

    /**
     * Back URL
     *
     * @var string
     */
    protected $backUrl;

    /**
     * Whether to do redirect to back URL
     *
     * @var bool
     */
    protected static $isRedirect = false;

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

        if (isset($values['useSession']) && $values['useSession']) {
            $this->useSession = true;
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
        if ($this->useSession) {
            $session = $this->getSession($request);
            $backUrl = $request->get($this->parameterName);

            // add URL to session
            if ($backUrl) {
                $session->set($this->parameterName, $backUrl);
            }

            // save back URL
            if ($session->has($this->parameterName)) {
                $url = $session->get($this->parameterName);
                $this->backUrl = $url;
            }
        } else {
            $this->backUrl = $request->get($this->parameterName);
        }
    }

    /**
     * @param Request $request
     * @return SessionInterface
     */
    protected function getSession(Request $request)
    {
        $session = $request->getSession();
        $session->start();
        return $session;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function process(Request $request, Response $response)
    {
        if (!self::$isRedirect || !$this->backUrl) {
            return $response;
        }

        $this->clear($request);

        return $this->getRedirectResponse($this->backUrl);
    }

    /**
     * @param string $backUrl
     * @return RedirectResponse
     */
    protected function getRedirectResponse($backUrl)
    {
        return new RedirectResponse($backUrl);
    }

    /**
     * @static
     * @param bool $flag
     */
    public static function triggerRedirect($flag = true)
    {
        self::$isRedirect = $flag;
    }

    /**
     * @param Request $request
     */
    protected function clear(Request $request)
    {
        if ($this->useSession) {
            $session = $this->getSession($request);
            if ($session->has($this->parameterName)) {
                $session->remove($this->parameterName);
            }
        }
    }
}
