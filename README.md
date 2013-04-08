Symfony 2 Back URL Annotation
=============================

This bundle allows to manage automatic redirects according to URL parameters.
To do this you should use special annotation @BackUrl for controller class or controller action.

To process redirect you should specify parameter name which contains redirect URL.
Redirect will be performed after processing of controller action.

*Note:* Annotation for controller action has higher priority than annotation for controller class.

Installation
------------

1. Add bundle name to "require" section in composer.json:
```
"ys-tools/back-url-bundle": "dev-master"
```
2. Update composer packages:
```
composer update
```
3. Register bundle in kernel (defaults - file app\AppKernel.php, method registerBundles):
```
new YsTools\BackUrlBundle\YsToolsBackUrlBundle(),
```

Parameters
----------

* **parameter**, *default name* (default: "backUrl") - name of the parameter which will be user to get back URL;
* **force** (default: false) - flag which specified that response will be overridden
even if original response wasn't successful (for example, redirect or error);
* **useFlashBag** (default: false) - flag which specified that back URL will be stored in
[session FlashBag](http://symfony.com/doc/master/components/http_foundation/sessions.html#flash-messages)
and redirect will be performed only after removing of URL attribute from FlashBag;

Examples
--------

#### General Controller Action Annotation

This annotation will automatically redirect user to URL specified in parameter "redirect"
after processing of listAction controller action.

``` php
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use YsTools\BackUrlBundle\Annotation\BackUrl;

class BlogController extends Controller
{
    /**
     * @BackUrl("redirect")
     */
    public function listAction()
    {
        ...
    }
}
```

#### Force Controller Class Annotation

This annotation will automatically redirect user to URL specified in parameter "back_to"
after processing of any controller action despite the original response.

``` php
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use YsTools\BackUrlBundle\Annotation\BackUrl;

/**
 * @BackUrl("back_to", force=true)
 */
class BlogController extends Controller
{
    ...
}
```

#### FlashBag Controller Action Annotation

This annotation will save back URL from parameter "href" in FlashBag object and will perform redirect
only if user enters correct data and FlashBag has cleared. Original redirect response will be overridden by
Back URL response.

``` php
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use YsTools\BackUrlBundle\Annotation\BackUrl;

class BlogController extends Controller
{
    /**
     * @BackUrl("href", force=true, useFlashBag=true)
     */
    public function postAction(Request $request)
    {
        // form initialization...

        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                // save data to DB...

                /** @var $session \Symfony\Component\HttpFoundation\Session\Session */
                $session = $request->getSession();
                $session->getFlashBag()->clear();

                return $this->redirect($this->generateUrl('blog_list'));
            }
        }

        return $this->render(
            'AcmeBlogBundle:Blog:post.html.twig',
            array('form' => $form->createView())
        );
    }
}
