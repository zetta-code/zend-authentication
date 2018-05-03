<?php
/**
 * @link      http://github.com/zetta-repo/zend-authentication for the canonical source repository
 * @copyright Copyright (c) 2017 Zetta Code
 */

namespace Zetta\ZendAuthentication\View;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class UnauthorizedStrategyFactory implements FactoryInterface
{
    /**
     * @inheritdoc
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->has('config') ? $container->get('config') : [];

        if (isset($config['view_manager'])
            && (is_array($config['view_manager'])
                || $config['view_manager'] instanceof \ArrayAccess
            )
        ) {
            $config = $config['view_manager'];
        } else {
            $config = [];
        }

        $template = isset($config['unauthorized_template']) ? $config['unauthorized_template'] : 'error/403';

        return new $requestedName($template);
    }
}
