<?php
/**
 * @link      http://github.com/zetta-code/zend-authentication for the canonical source repository
 * @copyright Copyright (c) 2018 Zetta Code
 */

declare(strict_types=1);

namespace Zetta\ZendAuthentication\Authentication\Storage;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zetta\ZendAuthentication\Options\Authentication;

class SessionFactory implements FactoryInterface
{
    /**
     * @inheritdoc
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        if (!is_null($options)) {
            if (isset($options['namespace'])) {
                $namespace = $options['namespace'];
            } else {
                $namespace = null;
            }

            if (isset($options['member'])) {
                $member = $options['member'];
            } else {
                $member = null;
            }

            if (isset($options['manager'])) {
                $manager = $options['manager'];
            } else {
                $manager = null;
            }
        } else {
            $namespace = null;
            $member = null;
            $manager = null;
        }

        $options = $this->getOptions($container);
        return new $requestedName($namespace, $member, $manager, $options);
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
