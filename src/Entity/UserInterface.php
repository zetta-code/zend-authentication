<?php
/**
 * @link      http://github.com/zetta-repo/zend-authentication for the canonical source repository
 * @copyright Copyright (c) 2017 Zetta Code
 */

namespace Zetta\ZendAuthentication\Entity;

/**
 * Interface UserInterface
 */
interface UserInterface
{
    /**
     * Get the User id
     * @return int
     */
    public function getId();

    /**
     * Set the User id
     * @param int $id
     * @return UserInterface
     */
    public function setId($id);

    /**
     * Get the User name
     * @return string
     */
    public function getName();

    /**
     * Set the User name
     * @param string $name
     * @return UserInterface
     */
    public function setName($name);

    /**
     * Get the User username
     * @return string
     */
    public function getUsername();

    /**
     * Set the User username
     * @param string $username
     * @return UserInterface
     */
    public function setUsername($username);

    /**
     * Get the User email
     * @return string
     */
    public function getEmail();

    /**
     * Set the User email
     * @param string $email
     * @return UserInterface
     */
    public function setEmail($email);

    /**
     * Get the User avatar
     * @param string $prefix
     * @return string
     */
    public function getAvatar($prefix = 'public');

    /**
     * Set the User avatar
     * @param string $avatar
     * @param bool $overwrite
     * @param string $dir
     * @return UserInterface
     */
    public function setAvatar($avatar, $overwrite = true, $dir = 'upload');

    /**
     * Get the User token
     * @return string
     */
    public function getToken();

    /**
     * Set the User token
     * @param string $token
     * @return UserInterface
     */
    public function setToken($token);

    /**
     * Get the User confirmedEmail
     * @return bool
     */
    public function isConfirmedEmail();

    /**
     * Set the User confirmedEmail
     * @param bool $confirmedEmail
     * @return UserInterface
     */
    public function setConfirmedEmail($confirmedEmail);

    /**
     * Get the User signAllowed
     * @return bool
     */
    public function isSignAllowed();

    /**
     * Set the User signAllowed
     * @param bool $signAllowed
     * @return UserInterface
     */
    public function setSignAllowed($signAllowed);

    /**
     * Get the User role name
     * @param mixed $role
     * @return string
     */
    public function role($role = null);
}
