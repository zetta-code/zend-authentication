<?php
/**
 * @link      http://github.com/zetta-repo/zend-authentication for the canonical source repository
 * @copyright Copyright (c) 2017 Zetta Code
 */

namespace Zetta\ZendAuthentication\View;

use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\Http\Response as HttpResponse;
use Zend\Mvc\MvcEvent;
use Zend\Stdlib\ResponseInterface as Response;
use Zend\View\Model\ViewModel;

class UnauthorizedStrategy extends AbstractListenerAggregate
{
    /**
     * @var string
     */
    protected $template = 'error/403';

    /**
     * UnauthorizedStrategy constructor.
     * @param string $template
     */
    public function __construct($template)
    {
        $this->template = (string) $template;
    }

    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH_ERROR, [$this, 'prepareExceptionViewModel'], -5000);
    }

    /**
     * Get the UnauthorizedStrategy template
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Set the UnauthorizedStrategy template
     * @param string $template
     * @return UnauthorizedStrategy
     */
    public function setTemplate($template)
    {
        $this->template = $template;
        return $this;
    }

    /**
     * Create an exception view model, and set the HTTP status code
     *
     * @todo   dispatch.error does not halt dispatch unless a response is
     *         returned. As such, we likely need to trigger rendering as a low
     *         priority dispatch.error event (or goto a render event) to ensure
     *         rendering occurs, and that munging of view models occurs when
     *         expected.
     * @param  MvcEvent $e
     * @return void
     */
    public function prepareExceptionViewModel(MvcEvent $e)
    {
        // Do nothing if no error in the event
        $error = $e->getError();
        if (empty($error)) {
            return;
        }

        // Do nothing if the result is a response object
        $result = $e->getResult();
        if ($result instanceof Response) {
            return;
        }

        switch ($error) {
            case 'not-allow':
                $model = new ViewModel([
                    'message'    => 'An error occurred during execution; please try again later.',
                    'role'       => $e->getParam('role'),
                    'controller' => $e->getParam('controller'),
                    'action'     => $e->getParam('action'),
                ]);
                $model->setTemplate($this->getTemplate());
                $e->setResult($model);
                $e->getViewModel()->addChild($model);

                $response = $e->getResponse();
                if (! $response) {
                    $response = new HttpResponse();
                    $response->setStatusCode(403);
                    $e->setResponse($response);
                } else {
                    $statusCode = $response->getStatusCode();
                    if ($statusCode === 200) {
                        $response->setStatusCode(403);
                    }
                }

                break;
            default:
                /*
                 * do nothing if there is no error in the event or the error
                 * does not match one of our predefined errors (we don't want
                 * our 403 template to handle other types of errors)
                 */
                return;
        }
    }
}
