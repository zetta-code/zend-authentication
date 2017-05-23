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
     * @return void
     * @throws \Exception
     */
    public function checkAuthentication(MvcEvent $e)
    {
        $matches = $e->getRouteMatch();
        if (!$matches instanceof RouteMatch) {
            return;
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
            return;
        }

        $controller = $matches->getParam('controller');
        $action = $matches->getParam('action');

        $config = $this->getServiceManager()->get('config');
        $auth = $this->getServiceManager()->get(AuthenticationService::class);
        $acl = $this->getServiceManager()->get(Permissions\Acl\Acl::class);

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
            return;
        }

        if (!$auth->hasIdentity()) {
            // Authentication
            if (!$acl->isAllowed($acl->getDefaultRole(), $controller, $action)) {
                /** @var FlashMessenger $flashMessenger */
                $flashMessenger = $this->getPluginManager()->get(FlashMessenger::class);
                $flashMessenger->addErrorMessage(_('Please, sign in.'));

                $router = $e->getRouter();
                $redirect = $e->getRequest()->getRequestUri();
                $params = ArrayUtils::merge($matches->getParams(), $config['zend_authentication']['routes']['signin']['params']);
                $options = [
                    'name' => $config['zend_authentication']['routes']['signin']['name']
                ];
                if ($redirect !== '' && $redirect !== '/') {
                    $options['query'] = ['redirect' => $e->getRequest()->getRequestUri()];
                }
                $options = ArrayUtils::merge($options, $config['zend_authentication']['routes']['signin']['options']);
                $url = $router->assemble($params, $options);
                /** @var \Zend\Http\PhpEnvironment\Response $response */
                $response = $e->getResponse();
                $response->getHeaders()->addHeaderLine('Location', $url);
                $response->setStatusCode(302);

                $e->stopPropagation();

                return;
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
                $navigation = $this->getHelperManager()->get('navigation');
                $navigation->setAcl($acl)->setRole($role);
            }
        }
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
