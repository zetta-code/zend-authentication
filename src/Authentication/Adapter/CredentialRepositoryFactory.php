<?php
/**
 * @link      http://github.com/zetta-repo/zend-authentication for the canonical source repository
 * @copyright Copyright (c) 2017 Zetta Code
 */

namespace Zetta\ZendAuthentication\Authentication\Adapter;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class CredentialRepositoryFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return CredentialRepository
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $configuration = $container->get('Configuration');
        $config = $configuration['zend_authentication'];
        $options = [
            'objectManager' => $container->get(EntityManager::class),
            'identityClass' => $config['options']['identityClass'],
            'identityProperty' => $config['options']['identityProperty'],
            'credentialClass' => $config['options']['credentialClass'],
            'credentialProperty' => $config['options']['credentialProperty'],
            'credentialIdentityProperty' => $config['options']['credentialIdentityProperty'],
            'credentialCallable' => $config['options']['credentialCallable'],
        ];

        return new CredentialRepository($options);
    }
}
