<?php
/**
 * @link      http://github.com/zetta-repo/zend-authentication for the canonical source repository
 * @copyright Copyright (c) 2017 Zetta Code
 */

namespace Zetta\ZendAuthentication\Controller;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Zend\Authentication\AuthenticationService;
use Zend\Mvc\I18n\Translator;
use Zend\ServiceManager\Factory\FactoryInterface;

class AuthControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get(EntityManager::class);
        $authenticationService = $container->get(AuthenticationService::class);
        $translator = $container->get(Translator::class);
        $configuration = $container->get('Configuration');
        $config = $configuration['zend_authentication'];

        return new AuthController($entityManager, $authenticationService, $translator,$config);
    }
}
