<?php
/**
 * @link      http://github.com/zetta-code/zend-authentication for the canonical source repository
 * @copyright Copyright (c) 2018 Zetta Code
 */

declare(strict_types=1);

namespace Zetta\ZendAuthentication\Permissions\Acl;

use Exception;
use Zend\Permissions\Acl\Acl as ZendAcl;
use Zend\Permissions\Acl\Resource\GenericResource;
use Zend\Permissions\Acl\Role\GenericRole;

/**
 * Class Acl.
 */
class Acl extends ZendAcl
{
    /**
     * @var string
     */
    protected $defaultRole = 'Guest';

    /**
     * Acl constructor.
     * @param array $config
     * @throws Exception
     */
    public function __construct($config = [])
    {
        if (!isset($config['roles']) || !isset($config['resources'])) {
            throw new Exception('Invalid ACL Config found');
        }

        $roles = $config['roles'];
        $resources = $config['resources'];
        if (isset($config['defaultRole'])) {
            $this->defaultRole = (string)$config['defaultRole'];
        }

        if (!isset($roles[$this->defaultRole])) {
            $roles[$this->defaultRole] = '';
        }

        $this->addRoles($roles);
        $this->addResources($resources);
    }

    /**
     * Adds Roles to ACL
     *
     * @param array $roles
     * @return Acl
     */
    protected function addRoles($roles)
    {
        foreach ($roles as $name => $parent) {
            if (!$this->hasRole($name)) {
                if (empty($parent) && !is_array($parent)) {
                    $parent = [];
                }
                $this->addRole(new GenericRole($name), $parent);
            }
        }

        return $this;
    }

    /**
     * Adds Resources to ACL
     *
     * @param $resources
     * @return Acl
     * @throws Exception
     */
    protected function addResources($resources)
    {
        foreach ($resources as $permission => $controllers) {
            foreach ($controllers as $controller => $actions) {
                if ($controller == '') {
                    $controller = null;
                } else {
                    if (!$this->hasResource($controller)) {
                        $this->addResource(new GenericResource($controller));
                    }
                }

                foreach ($actions as $action => $roles) {
                    if ($action == '') {
                        $action = null;
                    }

                    if ($permission == 'allow') {
                        foreach ($roles as $role) {
                            $this->allow($role, $controller, $action);
                        }
                    } elseif ($permission == 'deny') {
                        foreach ($roles as $role) {
                            $this->deny($role, $controller, $action);
                        }
                    } else {
                        throw new Exception('No valid permission defined: ' . $permission);
                    }
                }

            }
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultRole()
    {
        if (is_string($this->defaultRole)) {
            $this->defaultRole = new GenericRole($this->defaultRole);
        }

        return $this->defaultRole;
    }

    /**
     * @param mixed $defaultRole
     */
    public function setDefaultRole($defaultRole)
    {
        $this->defaultRole = $defaultRole;
    }
}
