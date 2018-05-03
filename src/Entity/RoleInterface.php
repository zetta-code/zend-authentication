<?php
/**
 * @link      http://github.com/zetta-code/zend-authentication for the canonical source repository
 * @copyright Copyright (c) 2018 Zetta Code
 */

namespace Zetta\ZendAuthentication\Entity;

/**
 * Interface RoleInterface
 */
interface RoleInterface
{
    /**
     * Get the Role id
     * @return int
     */
    public function getId();

    /**
     * Set the Role id
     * @param int $id
     * @return RoleInterface
     */
    public function setId($id);

    /**
     * Get the Role name
     * @return string
     */
    public function getName();

    /**
     * Set the Role name
     * @param string $name
     * @return RoleInterface
     */
    public function setName($name);

    /**
     * Get the Role defaultName
     * @return string
     */
    public function getDefaultName();
}
