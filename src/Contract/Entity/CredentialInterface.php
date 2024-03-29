<?php

/**
 * @link      https://github.com/zetta-code/zend-authentication for the canonical source repository
 * @copyright Copyright (c) 2018 Zetta Code
 */

declare(strict_types=1);

namespace Zetta\ZendAuthentication\Contract\Entity;

/**
 * Interface CredentialInterface.
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
     * Hash the Credential value
     * @return CredentialInterface
     */
    public function hashValue();

    /**
     * Verify the Credential value
     * @return bool
     */
    public function verifyValue($value);

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
     * @param string $value
     * @return bool
     */
    public static function check(UserInterface $user, CredentialInterface $credential, $value);
}
