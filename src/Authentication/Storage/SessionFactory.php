<?php
/**
 * @link      http://github.com/zetta-repo/zend-authentication for the canonical source repository
 * @copyright Copyright (c) 2017 Zetta Code
 */

namespace Zetta\ZendAuthentication\Authentication\Storage;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class SessionFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return Session
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

        $configuration = $container->get('Configuration');
        $config = $configuration['zend_authentication'];
        $options = [
            'objectManager' => $container->get(EntityManager::class),
            'identityClass' => $config['options']['identityClass'],
        ];

        return new Session($namespace, $member, $manager, $options);
    }

}
