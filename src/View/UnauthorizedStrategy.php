<?php

/**
 * @link      https://github.com/zetta-code/zend-authentication for the canonical source repository
 * @copyright Copyright (c) 2018 Zetta Code
 */

declare(strict_types=1);

namespace Zetta\ZendAuthentication\View;

use Laminas\EventManager\AbstractListenerAggregate;
use Laminas\EventManager\EventManagerInterface;
use Laminas\Http\Response as HttpResponse;
use Laminas\Mvc\MvcEvent;
use Laminas\Stdlib\ResponseInterface as Response;
use Laminas\View\Model\ViewModel;

/**
 * Class UnauthorizedStrategy.
 */
class UnauthorizedStrategy extends AbstractListenerAggregate
{
    public const NOT_ALLOW = 'not-allow';

    /**
     * Display exceptions?
     * @var bool
     */
    protected $displayExceptions = true;

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
        $this->template = (string)$template;
    }

    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH_ERROR, [$this, 'prepareExceptionViewModel'], -5000);
    }

    /**
     * Flag: display exceptions in error pages?
     *
     * @param bool $displayExceptions
     * @return UnauthorizedStrategy
     */
    public function setDisplayExceptions($displayExceptions)
    {
        $this->displayExceptions = (bool)$displayExceptions;
        return $this;
    }

    /**
     * Should we display exceptions in error pages?
     *
     * @return bool
     */
    public function displayExceptions()
    {
        return $this->displayExceptions;
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
     * @param MvcEvent $e
     * @return void
     * @todo   dispatch.error does not halt dispatch unless a response is
     *         returned. As such, we likely need to trigger rendering as a low
     *         priority dispatch.error event (or goto a render event) to ensure
     *         rendering occurs, and that munging of view models occurs when
     *         expected.
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
            case self::NOT_ALLOW:
                $exception = $e->getParam('exception');
                $model = new ViewModel([
                    'code' => $exception->getCode(),
                    'message' => $e->getParam('message'),
                    'role' => $e->getParam('role'),
                    'controller' => $e->getParam('controller'),
                    'action' => $e->getParam('action'),
                    'exception' => $exception,
                    'display_exceptions' => $this->displayExceptions(),
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
