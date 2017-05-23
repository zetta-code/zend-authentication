<?php
/**
 * @link      http://github.com/zetta-repo/zend-authentication for the canonical source repository
 * @copyright Copyright (c) 2017 Zetta Code
 */

namespace Zetta\ZendAuthentication\Entity;

/**
 * Interface CredentialInterface
 */
interface CredentialInterface
{
    /**
     * Get the Credential id
     * @return int
     */
    public function getId();

    /**
     * Set the Credential id
     * @param int $id
     * @return CredentialInterface
     */
    public function setId($id);

    /**
     * Get the Credential user
     * @return UserInterface
     */
    public function getUser();

    /**
     * Set the Credential user
     * @param UserInterface $user
     * @return CredentialInterface
     */
    public function setUser($user);

    /**
     * Get the Credential type
     * @return int
     */
    public function getType();

    /**
     * Set the Credential type
     * @param int $type
     * @return CredentialInterface
     */
    public function setType($type);

    /**
     * Get the Credential value
     * @return string
     */
    public function getValue();

    /**
     * Set the Credential value
     * @param string $value
     * @return CredentialInterface
     */
    public function setValue($value);

    /**
     * Get the Credential active
     * @return bool
     */
    public function isActive();

    /**
     * Set the Credential active
     * @param bool $active
     * @return CredentialInterface
     */
    public function setActive($active);

    /**
     * Check a credential
     * @param UserInterface $user
     * @param CredentialInterface $credential
     * @return bool
     */
    public static function check(UserInterface $user, CredentialInterface $credential);
}
