<?php
/**
 * @link      http://github.com/zetta-code/zend-authentication for the canonical source repository
 * @copyright Copyright (c) 2018 Zetta Code
 */

declare(strict_types=1);

namespace Zetta\ZendAuthentication;

use Exception;
use Zend\Authentication\AuthenticationService;
use Zend\Http\Response;
use Zend\Mvc\ApplicationInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\Controller\PluginManager as ControllerPluginManager;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Router\Http\RouteMatch;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Session\SessionManager;
use Zend\Stdlib\ArrayObject;
use Zend\Stdlib\ArrayUtils;
use Zend\View\HelperPluginManager;
use Zetta\ZendAuthentication\Exception\ResourceNotFoundException;
use Zetta\ZendAuthentication\Exception\UnauthorizedException;
use Zetta\ZendAuthentication\View\UnauthorizedStrategy;

/**
 * Class Module.
 */
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
     * @param MvcEvent $e The MvcEvent instance
     * @return void
     */
    public function onBootstrap($e)
    {
        $this->application = $e->getApplication();
        $eventManager = $this->application->getEventManager();
        $sharedEventManager = $eventManager->getSharedManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        $this->getServiceManager()->get(SessionManager::class);

        $sharedEventManager->attach(AbstractActionController::class, MvcEvent::EVENT_DISPATCH, [$this, 'onDispatch'], 100);
        $this->getServiceManager()->get(UnauthorizedStrategy::class)->attach($eventManager);
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
     * @param MvcEvent $event
     * @throws Exception
     */
    public function onDispatch(MvcEvent $event)
    {
        return $this->checkAuthentication($event);
    }

    /**
     * @param MvcEvent $event
     * @throws Exception
     */
    public function checkAuthentication(MvcEvent $event)
    {
        // Do nothing if no route match
        $routeMatch = $event->getRouteMatch();
        if (!$routeMatch instanceof RouteMatch) {
            return;
        }

        // Do nothing if the result is a response object
        $result = $event->getResult();
        if ($result instanceof Response) {
            return;
        }

        // Not handling
        if ($event->getError()) {
            return;
        }

        $targetController = $event->getTarget();
        $controller = $routeMatch->getParam('controller');
        $action = $routeMatch->getParam('action');
        $config = $this->getServiceManager()->get('config');
        $auth = $this->getServiceManager()->get(AuthenticationService::class);
        $acl = $this->getServiceManager()->get(Permissions\Acl\Acl::class);
        $navigation = $this->getHelperManager()->get('navigation');
        if ($auth->hasIdentity()) {
            $identity = $auth->getIdentity();
            $role = $auth->getIdentity()->role();
        } else {
            $identity = null;
            $role = $acl->getDefaultRole();
        }

        $navigation->setAcl($acl)->setRole($role);

        if (!$acl->hasResource($controller)) {
            $event->setName(MvcEvent::EVENT_DISPATCH_ERROR);
            $event->setError(UnauthorizedStrategy::NOT_ALLOW);
            $event->setController($controller);
            $event->setControllerClass($controller);
            $event->setParam('message', 'Resource not found.');
            $event->setParam('exception', new ResourceNotFoundException(sprintf('ACL Resource %s not found', $controller), Response::STATUS_CODE_501));
            $event->setParam('controller', $controller);
            $return = $this->application->getEventManager()->triggerEvent($event)->last();
            if (!$return) {
                $return = $event->getResult();
            }
            if (!is_object($return)) {
                if (ArrayUtils::hasStringKeys($return)) {
                    $return = new ArrayObject($return, ArrayObject::ARRAY_AS_PROPS);
                }
            }
            $event->setResult($return);
            $event->stopPropagation(true);
            return;
        }

        if ($identity === null) {
            // Authentication
            if (!$acl->isAllowed($role, $controller, $action)) {
                $targetController->flashMessenger()->addErrorMessage(_('Please, sign in.'));

                $uri = $event->getApplication()->getRequest()->getUri();
                $uri->setScheme(null)
                    ->setHost(null)
                    ->setPort(null)
                    ->setUserInfo(null);
                $redirectUri = $targetController->url()->fromRoute($config['zend_authentication']['routes']['redirect']['name'], $config['zend_authentication']['routes']['redirect']['params'], $config['zend_authentication']['routes']['redirect']['options'], $config['zend_authentication']['routes']['redirect']['reuseMatchedParams']);
                $options = [];
                if ($uri->toString() !== '' && $uri !== $redirectUri) {
                    $options['query'] = ['redirect' => $uri->toString()];
                }
                $options = ArrayUtils::merge($config['zend_authentication']['routes']['signin']['options'], $options);
                return $targetController->redirect()->toRoute($config['zend_authentication']['routes']['signin']['name'], $config['zend_authentication']['routes']['signin']['params'], $options, $config['zend_authentication']['routes']['signin']['reuseMatchedParams']);
            }
        } else {
            // Authorization
            if (!$acl->isAllowed($role, $controller, $action)) {
                $event->setName(MvcEvent::EVENT_DISPATCH_ERROR);
                $event->setError(UnauthorizedStrategy::NOT_ALLOW);
                $event->setController($controller);
                $event->setControllerClass($controller);
                $event->setParam('message', 'You are not authorized.');
                $event->setParam('exception', new UnauthorizedException(sprintf('You are not authorized to access %s:%s', $controller, $action), Response::STATUS_CODE_403));
                $event->setParam('identity', $identity);
                $event->setParam('controller', $controller);
                $event->setParam('action', $action);
                $return = $this->application->getEventManager()->triggerEvent($event)->last();
                if (!$return) {
                    $return = $event->getResult();
                }
                if (!is_object($return)) {
                    if (ArrayUtils::hasStringKeys($return)) {
                        $return = new ArrayObject($return, ArrayObject::ARRAY_AS_PROPS);
                    }
                }
                $event->setResult($return);
                $event->stopPropagation(true);
            } else {
                $navigation->setRole($role);
            }
        }

        return;
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
