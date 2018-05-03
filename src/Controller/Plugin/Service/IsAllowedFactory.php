<?php
/**
 * @link      http://github.com/zetta-repo/zend-authentication for the canonical source repository
 * @copyright Copyright (c) 2017 Zetta Code
 */

namespace Zetta\ZendAuthentication\Controller\Plugin\Service;

use Interop\Container\ContainerInterface;
use Zend\Authentication\AuthenticationService;
use Zend\Permissions\Acl\Role\GenericRole;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zetta\ZendAuthentication\Permissions\Acl\Acl;

class IsAllowedFactory implements FactoryInterface
{
    /**
     * @inheritdoc
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $auth = $container->get(AuthenticationService::class);
        $acl = $container->get(Acl::class);
        $role = $auth->hasIdentity() ? new GenericRole($auth->getIdentity()->getRoleName()) : $acl->getDefaultRole();

        return new $requestedName($acl, $role);
    }
}
