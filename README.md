Symfony 2 Back URL Annotation
=============================

This bundle allows to manage automatic redirects according to URL parameters.
To do this you should use special annotation @BackUrl for controller class or controller action.

To process redirect you should specify parameter name which contains redirect URL.
Redirect will be performed after processing of controller action.
If there is no redirect parameter in URL, redirect will not be performed.
Default parameter name is "backUrl".

Note: Annotation for controller action has higher priority than annotation for controller class.

#### Controller Method Annotation

This annotation will automatically redirect user to URL specified in parameter "redirect"
after processing of listAction controller action.

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

#### Controller Class Annotation

This annotation will automatically redirect user to URL specified in parameter "back_to"
after processing of any controller action.

    use Symfony\Bundle\FrameworkBundle\Controller\Controller;
    use YsTools\BackUrlBundle\Annotation\BackUrl;

    /**
     * @BackUrl("back_to")
     */
    class BlogController extends Controller
    {
        ...
    }
