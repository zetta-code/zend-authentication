<?php
/**
 * @link      http://github.com/zetta-code/zend-authentication for the canonical source repository
 * @copyright Copyright (c) 2018 Zetta Code
 */

declare(strict_types=1);

namespace Zetta\ZendAuthentication\Authentication\Adapter;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zetta\ZendAuthentication\Options\Authentication;

class CredentialFactory implements FactoryInterface
{
    /**
     * @inheritdoc
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {

        $options = $this->getOptions($container);

        return new $requestedName($options);
    }

    /**
     * Gets options from configuration.
     *
     * @param  ContainerInterface $container
     * @return Authentication
     */
    public function getOptions(ContainerInterface $container)
    {
        $options = $container->get('config');
        $options = $options['zend_authentication']['options'];
        $options['objectManager'] = $container->get(EntityManager::class);

        return new Authentication($options);
    }
}
