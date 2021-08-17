<?php

/**
 * @link      https://github.com/zetta-code/zend-authentication for the canonical source repository
 * @copyright Copyright (c) 2018 Zetta Code
 */

declare(strict_types=1);

namespace Zetta\ZendAuthentication\Controller\Service;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Laminas\Authentication\AuthenticationService;
use Laminas\Mvc\I18n\Translator;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * Class AuthControllerFactory.
 */
class AuthControllerFactory implements FactoryInterface
{
    /**
     * @inheritdoc
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get(EntityManager::class);
        $authenticationService = $container->get(AuthenticationService::class);
        $translator = $container->get(Translator::class);
        $formManager = $container->get('FormElementManager');
        $config = $container->get('config');
        $config = $config['zend_authentication'];

        return new $requestedName($entityManager, $authenticationService, $translator, $formManager, $config);
    }
}
