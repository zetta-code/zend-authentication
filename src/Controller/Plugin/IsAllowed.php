<?php

/**
 * @link      https://github.com/zetta-code/zend-authentication for the canonical source repository
 * @copyright Copyright (c) 2018 Zetta Code
 */

declare(strict_types=1);

namespace Zetta\ZendAuthentication\Controller\Plugin;

use Laminas\Mvc\Controller\Plugin\AbstractPlugin;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use Laminas\Permissions\Acl\Role\RoleInterface;
use RuntimeException;
use Zetta\ZendAuthentication\Permissions\Acl\Acl;

/**
 * Class IsAllowed.
 */
class IsAllowed extends AbstractPlugin
{
    /**
     * @var Acl
     */
    protected $acl;

    /**
     * @var RoleInterface
     */
    protected $role;

    /**
     * IsAllowed constructor.
     * @param Acl $acl
     * @param RoleInterface $role
     */
    public function __construct(Acl $acl, RoleInterface $role)
    {
        $this->acl = $acl;
        $this->role = $role;
    }

    /**
     * @param null $resource
     * @param null $privilege
     * @return IsAllowed|bool
     */
    public function __invoke($resource = null, $privilege = null)
    {
        if ($resource === null) {
            return $this;
        }

        return $this->isAllowed($resource, $privilege);
    }

    /**
     * @param string|ResourceInterface $resource
     * @param string $privilege
     * @param string|RoleInterface $role
     * @return bool
     * @throws RuntimeException
     */
    public function isAllowed($resource, $privilege = null, $role = null)
    {
        if ($role === null) {
            $role = $this->getRole();
        }
        return $this->acl->isAllowed($role, $resource, $privilege);
    }

    /**
     * @param Acl $acl
     * @return IsAllowed
     */
    public function setAcl(Acl $acl = null)
    {
        $this->acl = $acl;
        return $this;
    }

    /**
     * @return Acl
     */
    protected function getAcl()
    {
        return $this->acl;
    }

    /**
     * @param RoleInterface $role
     * @return IsAllowed
     */
    public function setRole(RoleInterface $role = null)
    {
        $this->role = $role;
        return $this;
    }

    /**
     * @return RoleInterface
     */
    protected function getRole()
    {
        return $this->role;
    }
}
