<?php

/**
 * @link      https://github.com/zetta-code/zend-authentication for the canonical source repository
 * @copyright Copyright (c) 2018 Zetta Code
 */

declare(strict_types=1);

namespace Zetta\ZendAuthentication\Authentication;

use Interop\Container\ContainerInterface;
use Laminas\Authentication\AuthenticationService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Zetta\ZendAuthentication\Authentication\Adapter\Credential;
use Zetta\ZendAuthentication\Authentication\Storage\Session;

/**
 * Class AuthenticationServiceFactory.
 */
class AuthenticationServiceFactory implements FactoryInterface
{
    /**
     * @inheritdoc
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $storage = $container->get(Session::class);
        $adapter = $container->get(Credential::class);

        return new AuthenticationService($storage, $adapter);
    }
}
