<?php
/**
 * @link      http://github.com/zetta-repo/zend-authentication for the canonical source repository
 * @copyright Copyright (c) 2017 Zetta Code
 */

namespace Zetta\ZendAuthentication\Authentication;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zetta\ZendAuthentication\Authentication\Adapter\Credential;
use Zetta\ZendAuthentication\Authentication\Storage\Session;

class AuthenticationServiceFactory implements FactoryInterface
{
    /**
     * @inheritdoc
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $storage = $container->get(Session::class);
        $adapter = $container->get(Credential::class);

        return new $requestedName($storage, $adapter);
    }
}
