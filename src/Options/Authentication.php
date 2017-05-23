<?php
/**
 * @link      http://github.com/zetta-repo/zend-authentication for the canonical source repository
 * @copyright Copyright (c) 2017 Zetta Code
 */

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
}
