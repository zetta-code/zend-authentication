<?php
/**
 * @link      http://github.com/zetta-code/zend-authentication for the canonical source repository
 * @copyright Copyright (c) 2018 Zetta Code
 */

declare(strict_types=1);

namespace Zetta\ZendAuthentication;

use Zend\Authentication\AuthenticationService;

return [
    'controllers' => [
        'aliases' => [
            'Zetta\ZendAuthentication\Controller\Account' => Controller\AccountController::class,
            'Zetta\ZendAuthentication\Controller\Auth' => Controller\AuthController::class
        ],
        'factories' => [
            Controller\AccountController::class => Controller\Service\AccountControllerFactory::class,
            Controller\AuthController::class => Controller\Service\AuthControllerFactory::class
        ]
    ],

    'controller_plugins' => [
        'aliases' => [
            'isAllowed' => Controller\Plugin\IsAllowed::class,
        ],
        'factories' => [
            Controller\Plugin\IsAllowed::class => Factory\IsAllowedFactory::class,
        ]
    ],

    'service_manager' => [
        'factories' => [
            Authentication\Adapter\Credential::class => Authentication\Adapter\CredentialFactory::class,
            Authentication\Storage\Session::class => Authentication\Storage\SessionFactory::class,
            AuthenticationService::class => Authentication\AuthenticationServiceFactory::class,
            Permissions\Acl\Acl::class => Permissions\Acl\AclFactory::class,
            View\UnauthorizedStrategy::class => View\UnauthorizedStrategyFactory::class
        ]
    ],

    'view_helpers' => [
        'aliases' => [
            'isAllowed' => View\Helper\IsAllowed::class,
        ],
        'factories' => [
            View\Helper\IsAllowed::class => Factory\IsAllowedFactory::class,
        ]
    ],

    'view_manager' => [
        'controller_map' => [
            'Zetta\ZendAuthentication' => true,
        ],
        'template_path_stack' => [
            __DIR__ . '/../view'
        ]
    ]
];
