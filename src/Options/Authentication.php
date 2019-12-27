<?php
/**
 * @link      http://github.com/zetta-code/zend-authentication for the canonical source repository
 * @copyright Copyright (c) 2018 Zetta Code
 */

declare(strict_types=1);

namespace Zetta\ZendAuthentication\Options;

use Doctrine\Common\Persistence\ObjectRepository;
use DoctrineModule\Options\Authentication as AuthenticationOptions;
use Zend\Authentication\Adapter\Exception;

/**
 * Authentication options that uses a Doctrine object for verification.
 */
class Authentication extends AuthenticationOptions
{
    /**
     * Property to use for the credential email
     *
     * @var string
     */
    protected $emailProperty;

    /**
     * A valid object implementing ObjectRepository interface (or ObjectManager/identityClass)
     *
     * @var ObjectRepository
     */
    protected $credentialRepository;

    /**
     * Credential's class name
     *
     * @var string
     */
    protected $credentialClass;

    /**
     * Property to use for the credential identity
     *
     * @var string
     */
    protected $credentialIdentityProperty;

    /**
     * Credential type
     *
     * @var string
     */
    protected $credentialType;

    /**
     * Property to use for the credential type
     *
     * @var string
     */
    protected $credentialTypeProperty;

    /**
     * Role's class name
     *
     * @var string
     */
    protected $roleClass;

    /**
     * @return ObjectRepository
     */
    public function getCredentialRepository()
    {
        if ($this->credentialRepository) {
            return $this->credentialRepository;
        }

        return $this->objectManager->getRepository($this->credentialClass);
    }

    /**
     * @param  ObjectRepository $credentialRepository
     * @return Authentication
     */
    public function setCredentialRepository(ObjectRepository $credentialRepository)
    {
        $this->credentialRepository = $credentialRepository;
        return $this;
    }

    /**
     * @return string
     */
    public function getCredentialClass()
    {
        return $this->credentialClass;
    }

    /**
     * @param string $credentialClass
     * @return Authentication
     */
    public function setCredentialClass($credentialClass)
    {
        $this->credentialClass = $credentialClass;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmailProperty()
    {
        return $this->emailProperty;
    }

    /**
     * @param  string $emailProperty
     * @throws Exception\InvalidArgumentException
     * @return Authentication
     */
    public function setEmailProperty($emailProperty)
    {
        if (!is_string($emailProperty) || $emailProperty === '') {
            throw new Exception\InvalidArgumentException(
                sprintf('Provided $emailProperty is invalid, %s given', gettype($emailProperty))
            );
        }

        $this->emailProperty = $emailProperty;

        return $this;
    }

    /**
     * @return string
     */
    public function getCredentialIdentityProperty()
    {
        return $this->credentialIdentityProperty;
    }

    /**
     * @param string $credentialIdentityProperty
     * @return Authentication
     */
    public function setCredentialIdentityProperty($credentialIdentityProperty)
    {
        if (!is_string($credentialIdentityProperty) || $credentialIdentityProperty === '') {
            throw new Exception\InvalidArgumentException(
                sprintf('Provided $credentialIdentityProperty is invalid, %s given', gettype($credentialIdentityProperty))
            );
        }

        $this->credentialIdentityProperty = $credentialIdentityProperty;
        return $this;
    }

    /**
     * @return string
     */
    public function getCredentialType()
    {
        return $this->credentialType;
    }

    /**
     * @param  string $credentialType
     * @throws Exception\InvalidArgumentException
     * @return Authentication
     */
    public function setCredentialType($credentialType)
    {
        if (empty($credentialType)) {
            throw new Exception\InvalidArgumentException(
                sprintf('Provided $credentialType is invalid, %s given', gettype($credentialType))
            );
        }

        $this->credentialType = $credentialType;

        return $this;
    }

    /**
     * @return string
     */
    public function getCredentialTypeProperty()
    {
        return $this->credentialTypeProperty;
    }

    /**
     * @param  string $credentialTypeProperty
     * @throws Exception\InvalidArgumentException
     * @return Authentication
     */
    public function setCredentialTypeProperty($credentialTypeProperty)
    {
        if (!is_string($credentialTypeProperty) || $credentialTypeProperty === '') {
            throw new Exception\InvalidArgumentException(
                sprintf('Provided $credentialTypeProperty is invalid, %s given', gettype($credentialTypeProperty))
            );
        }

        $this->credentialTypeProperty = $credentialTypeProperty;

        return $this;
    }

    /**
     * @return string
     */
    public function getRoleClass()
    {
        return $this->roleClass;
    }

    /**
     * @param string $roleClass
     * @return Authentication
     */
    public function setRoleClass($roleClass)
    {
        $this->roleClass = $roleClass;
        return $this;
    }
}
