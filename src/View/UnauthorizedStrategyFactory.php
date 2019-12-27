<?php
/**
 * @link      http://github.com/zetta-code/zend-authentication for the canonical source repository
 * @copyright Copyright (c) 2018 Zetta Code
 */

declare(strict_types=1);

namespace Zetta\ZendAuthentication\View;

use ArrayAccess;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Class UnauthorizedStrategyFactory.
 */
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
                || $config['view_manager'] instanceof ArrayAccess
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
