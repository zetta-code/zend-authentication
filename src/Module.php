<?php
/**
 * @link      http://github.com/zetta-repo/zend-authentication for the canonical source repository
 * @copyright Copyright (c) 2017 Zetta Code
 */

namespace Zetta\ZendAuthentication;

use Zend\Authentication\AuthenticationService;
use Zend\Http\Response;
use Zend\Mvc\Application;
use Zend\Mvc\ApplicationInterface;
use Zend\Mvc\Controller\PluginManager as ControllerPluginManager;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Zend\Router\Http\RouteMatch;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Stdlib\ArrayObject;
use Zend\Stdlib\ArrayUtils;
use Zend\Stdlib\ResponseInterface;
use Zend\View\Helper\Url;
use Zend\View\HelperPluginManager;
use Zetta\ZendAuthentication\Exception\UnauthorizedException;
use Zetta\ZendAuthentication\View\UnauthorizedStrategy;

class Module
{
    /**
     * @var ApplicationInterface
     */
    protected $application;

    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceManager;

    /**
     * @var ControllerPluginManager
     */
    protected $pluginManager;

    /**
     * @var HelperPluginManager
     */
    protected $helperManager;

    /**
     * @param  \Zend\Mvc\MvcEvent $e The MvcEvent instance
     * @return void
     */
    public function onBootstrap($e)
    {
        $this->application = $e->getApplication();
        $eventeManager = $this->application->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventeManager);

        $this->getServiceManager()->get(UnauthorizedStrategy::class)->attach($eventeManager);

        $eventeManager->attach(MvcEvent::EVENT_ROUTE, [$this, 'checkAuthentication']);
    }

    /**
     * Provide application configuration.
     *
     * @return array
     */
    public function getConfig()
    {
        $provider = new ConfigProvider();
        return $provider->getConfig();
    }

    /**
     * @param MvcEvent $e
     * @return ResponseInterface
     * @throws \Exception
     */
    public function checkAuthentication(MvcEvent $e)
    {
        $matches = $e->getRouteMatch();
        if (!$matches instanceof RouteMatch) {
            return null;
        }

        //framework error
        $eventParams = $e->getParams();
        if (isset($eventParams['error'])) {
            /** @var \Zend\Http\PhpEnvironment\Response $response */
            $response = $e->getResponse();
            switch ($eventParams['error']) {
                case Application::ERROR_CONTROLLER_NOT_FOUND:
                    $response->setStatusCode(Response::STATUS_CODE_501);
                    break;

                case Application::ERROR_ROUTER_NO_MATCH:
                    $response->setStatusCode(Response::STATUS_CODE_501);
                    break;

                default:
                    $response->setStatusCode(Response::STATUS_CODE_500);
                    break;
            }
            $e->stopPropagation();
            return null;
        }

        $controller = $matches->getParam('controller');
        $action = $matches->getParam('action');

        $config = $this->getServiceManager()->get('config');
        $auth = $this->getServiceManager()->get(AuthenticationService::class);
        $acl = $this->getServiceManager()->get(Permissions\Acl\Acl::class);
        $navigation = $this->getHelperManager()->get('navigation');
        $role = $acl->getDefaultRole();
        $navigation->setAcl($acl)->setRole($role);

        if (!$acl->hasResource($controller)) {
            $e->setName(MvcEvent::EVENT_DISPATCH_ERROR);
            $e->setError(Application::ERROR_EXCEPTION);
            $e->setParam('exception', new \Exception('Resource ' . $controller . ' not defined', Response::STATUS_CODE_501));
            $return = $this->application->getEventManager()->triggerEvent($e);
            if (! $return) {
                $return = $e->getResult();
            }
            if (! is_object($return)) {
                if (ArrayUtils::hasStringKeys($return)) {
                    $return = new ArrayObject($return, ArrayObject::ARRAY_AS_PROPS);
                }
            }
            $e->setResult($return);
            return null;
        }

        if (!$auth->hasIdentity()) {
            // Authentication
            if (!$acl->isAllowed($role, $controller, $action)) {
                /** @var FlashMessenger $flashMessenger */
                $flashMessenger = $this->getPluginManager()->get(FlashMessenger::class);
                $flashMessenger->addErrorMessage(_('Please, sign in.'));
                /** @var Url $urlHelper */
                $urlHelper = $this->getHelperManager()->get(Url::class);

                $uri = $e->getRequest()->getRequestUri();
                $redirectUri = $urlHelper($config['zend_authentication']['routes']['redirect']['name'], $config['zend_authentication']['routes']['redirect']['params'], $config['zend_authentication']['routes']['redirect']['options'], $config['zend_authentication']['routes']['redirect']['reuseMatchedParams']);
                $options = [];
                if ($uri !== '' && $uri !== $redirectUri) {
                    $options['query'] = ['redirect' => $e->getRequest()->getRequestUri()];
                }
                $options = ArrayUtils::merge($config['zend_authentication']['routes']['signin']['options'], $options);
                $url = $urlHelper($config['zend_authentication']['routes']['signin']['name'], $config['zend_authentication']['routes']['signin']['params'], $options, $config['zend_authentication']['routes']['signin']['reuseMatchedParams']);
                /** @var \Zend\Http\PhpEnvironment\Response $response */
                $response = $e->getResponse();
                $response->getHeaders()->addHeaderLine('Location', $url);
                $response->setStatusCode(302);

                $e->stopPropagation();

                return $response;
            }
        } else {
            // Authorization
            $identity = $auth->getIdentity();
            $role = $auth->getIdentity()->role();

            if (!$acl->isAllowed($role, $controller, $action)) {
                $e->setName(MvcEvent::EVENT_DISPATCH_ERROR);
                $e->setError('not-allow');
                $e->setController($controller);
                $e->setControllerClass($controller);
                $e->setParam('exception', new UnAuthorizedException(sprintf('You are not authorized to access %s:%s', $controller, $action), 403));
                $e->setParam('identity', $identity);
                $e->setParam('controller', $controller);
                $e->setParam('action', $action);
                $return = $this->application->getEventManager()->triggerEvent($e)->last();
                if (! $return) {
                    $return = $e->getResult();
                }
                if (! is_object($return)) {
                    if (ArrayUtils::hasStringKeys($return)) {
                        $return = new ArrayObject($return, ArrayObject::ARRAY_AS_PROPS);
                    }
                }
                $e->setResult($return);
            } else {
                $navigation->setRole($role);
            }
        }

        return null;
    }

    /**
     * @return ServiceLocatorInterface
     */
    public function getServiceManager()
    {
        if ($this->serviceManager == null) {
            $this->serviceManager = $this->application->getServiceManager();
        }

        return $this->serviceManager;
    }

    /**
     * @return ControllerPluginManager
     */
    public function getPluginManager()
    {
        if ($this->pluginManager == null) {
            $this->pluginManager = $this->getServiceManager()->get('ControllerPluginManager');
        }

        return $this->pluginManager;
    }

    /**
     * @return HelperPluginManager
     */
    public function getHelperManager()
    {
        if ($this->helperManager == null) {
            $this->helperManager = $this->getServiceManager()->get('ViewHelperManager');
        }

        return $this->helperManager;
    }
}
