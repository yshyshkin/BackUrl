Symfony 2 Back URL Annotation
=============================

This bundle allows to manage automatic redirects according to URL parameters.
To do this you should use special annotation @BackUrl for controller class or controller action.

To process redirect you should specify parameter name which contains redirect URL.
In controller action code you can trigger back URL redirect in desired place,
and redirect will be performed after processing of controller action.

If URL doesn't contain back URL parameters, there won't be performed any additional actions.

*Note:* Annotation for controller action has higher priority than annotation for controller class.

Installation
------------

1. Add bundle name to "require" section in composer.json:
``"ys-tools/back-url-bundle": "dev-master"``

2. Update composer packages:
``composer update``

3. Register bundle in kernel (defaults - file app\AppKernel.php, method registerBundles):
``new YsTools\BackUrlBundle\YsToolsBackUrlBundle(),``

Parameters
----------

* **parameter**, *default name* (default: "backUrl") - name of the parameter which will be user to get back URL;
* **useSession** (default: false) - flag which specified that back URL will be stored in session
until the redirect will be performed.

Examples
--------

#### Controller Action Annotation

This annotation will redirect user to URL specified in parameter "redirect"
after processing of listAction controller action.

``` php
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use YsTools\BackUrlBundle\Annotation\BackUrl;

class BlogController extends Controller
{
    /**
     * @BackUrl("redirect")
     */
    public function postAction()
    {
        ...
        BackUrl::triggerRedirect();
        ...
    }
}
```

#### Controller Class Annotation

This annotation will redirect user to URL specified in parameter "back_to"
after processing of controller action which triggers back URL redirect.

``` php
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use YsTools\BackUrlBundle\Annotation\BackUrl;

/**
 * @BackUrl("back_to")
 */
class BlogController extends Controller
{
    ...

    public function listAction()
    {
        ...
    }

    ...

    public function postAction()
    {
        ...
        BackUrl::triggerRedirect();
        ...
    }

    ...
}
```

#### Controller Action Annotation using Session

This annotation will save back URL from parameter "href" in Session object and will perform redirect
when user triggers back URL redirect.
Back URL will be stored in Session object until back URL redirect will be performed.

``` php
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use YsTools\BackUrlBundle\Annotation\BackUrl;

class BlogController extends Controller
{
    /**
     * @BackUrl("href", useSession=true)
     */
    public function postAction(Request $request)
    {
        // form initialization...

        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                // save data to DB...

                BackUrl::triggerRedirect();

                return $this->redirect($this->generateUrl('blog_list'));
            }
        }

        return $this->render(
            'AcmeBlogBundle:Blog:post.html.twig',
            array('form' => $form->createView())
        );
    }
}
