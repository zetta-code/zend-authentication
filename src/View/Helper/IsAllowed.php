<?php
/**
 * @link      http://github.com/zetta-code/zend-authentication for the canonical source repository
 * @copyright Copyright (c) 2018 Zetta Code
 */

namespace Zetta\ZendAuthentication\View\Helper;

use RuntimeException;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Zend\Permissions\Acl\Role\RoleInterface;
use Zend\View\Helper\AbstractHelper;
use Zetta\ZendAuthentication\Permissions\Acl\Acl;

/**
 * Class IsAllowed.
 */
class IsAllowed extends AbstractHelper
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
     * @param string|RoleInterface $role
     * @return IsAllowed|bool
     */
    public function __invoke($resource = null, $privilege = null, $role = null)
    {
        if ($resource === null) {
            return $this;
        }

        if ($role === null) {
            $role = $this->getRole();
        }

        return $this->isAllowed($resource, $privilege, $role);
    }

    /**
     * @return RoleInterface
     */
    protected function getRole()
    {
        return $this->role;
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
     * @return Acl
     */
    protected function getAcl()
    {
        return $this->acl;
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
}
