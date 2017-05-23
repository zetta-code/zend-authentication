<?php
/**
 * @link      http://github.com/zetta-repo/zend-authentication for the canonical source repository
 * @copyright Copyright (c) 2017 Zetta Code
 */

namespace Zetta\ZendAuthentication\Authentication\Adapter;

use DoctrineModule\Authentication\Adapter\ObjectRepository;
use Zend\Authentication\Adapter\Exception;
use Zend\Authentication\Result as AuthenticationResult;
use Zetta\ZendAuthentication\Options\Authentication as AuthenticationOptions;

/**
 * Authentication adapter that uses a Doctrine object for verification.
 */
class CredentialRepository extends ObjectRepository
{
    /**
     * @var AuthenticationOptions
     */
    protected $options;

    /**
     * Constructor
     *
     * @param array|AuthenticationOptions $options
     */
    public function __construct($options = array())
    {
        parent::__construct($options);
    }

    /**
     * @param array|\DoctrineModule\Options\Authentication $options
     * @return $this
     */
    public function setOptions($options)
    {
        if (!$options instanceof AuthenticationOptions) {
            $options = new AuthenticationOptions($options);
        }

        $this->options = $options;
        return $this;
    }

    /**
     * Set the value to be used as the identity
     *
     * @param  mixed $identityValue
     * @return CredentialRepository
     * @deprecated use setIdentity instead
     */
    public function setIdentityValue($identityValue)
    {
        $this->identity = $identityValue;
        return $this;
    }

    /**
     * @return string
     * @deprecated use getIdentity instead
     */
    public function getIdentityValue()
    {
        return $this->identity;
    }

    /**
     * Set the credential value to be used.
     *
     * @param  mixed $credentialValue
     * @return CredentialRepository
     * @deprecated use setCredential instead
     */
    public function setCredentialValue($credentialValue)
    {
        $this->credential = $credentialValue;
        return $this;
    }

    /**
     * @return string
     * @deprecated use getCredential instead
     */
    public function getCredentialValue()
    {
        return $this->credential;
    }

    /**
     * {@inheritDoc}
     */
    public function authenticate()
    {
        $options = $this->options;
        if ($options->getIdentityClass() != null) {
            $this->setup();
            $identity = $options
                ->getObjectRepository()
                ->findOneBy([$options->getIdentityProperty() => $this->identity]);

            if (!$identity) {
                $this->authenticationResultInfo['code'] = AuthenticationResult::FAILURE_IDENTITY_NOT_FOUND;
                $this->authenticationResultInfo['messages'][] = 'A record with the supplied identity could not be found.';

                return $this->createAuthenticationResult();
            }

            $authResult = $this->validateIdentity($identity);
        } else {
            $this->setupCredential();
            $credential = $options
                ->getCredentialRepository()
                ->findOneBy([
                    $options->getIdentityProperty() => $this->identity,
                    $options->getCredentialProperty() => $this->credential
                ]);

            if (!$credential) {
                $this->authenticationResultInfo['code'] = AuthenticationResult::FAILURE_IDENTITY_NOT_FOUND;
                $this->authenticationResultInfo['messages'][] = 'A record with the supplied credential could not be found.';

                return $this->createAuthenticationResult();
            }
            $authResult = $this->validateCredential($credential);
        }

        return $authResult;
    }

    /**
     * This method attempts to validate that the record in the resultset is indeed a
     * record that matched the identity provided to this adapter.
     *
     * @param  object $identity
     * @throws Exception\UnexpectedValueException
     * @return AuthenticationResult
     */
    protected function validateIdentity($identity)
    {
        $options = $this->options;
        $credential = $options
            ->getCredentialRepository()
            ->findOneBy([
                $options->getCredentialIdentityProperty() => $identity,
                $options->getCredentialProperty() => $this->credential
            ]);

        if (!$credential) {
            $this->authenticationResultInfo['code'] = AuthenticationResult::FAILURE_IDENTITY_NOT_FOUND;
            $this->authenticationResultInfo['messages'][] = 'A record with the supplied credential could not be found.';

            return $this->createAuthenticationResult();
        }

        $credentialIdentityProperty = $this->options->getCredentialIdentityProperty();
        $getter = 'get' . ucfirst($credentialIdentityProperty);
        $credentialIdentity = null;

        if (method_exists($credential, $getter)) {
            $credentialIdentity = $credential->$getter();
        } elseif (property_exists($credential, $credentialIdentityProperty)) {
            $credentialIdentity = $credential->{$credentialIdentityProperty};
        } else {
            throw new Exception\UnexpectedValueException(
                sprintf(
                    'Property (%s) in (%s) is not accessible. You should implement %s::%s()',
                    $credentialIdentityProperty,
                    get_class($credential),
                    get_class($credential),
                    $getter
                )
            );
        }

        $callable = $this->options->getCredentialCallable();

        if ($callable) {
            $credentialValue = call_user_func($callable, $identity, $credential);
        } else {
            $credentialValue = $identity;
        }

        if ($credentialValue !== true && $credentialValue !== $credentialIdentity) {
            $this->authenticationResultInfo['code'] = AuthenticationResult::FAILURE_CREDENTIAL_INVALID;
            $this->authenticationResultInfo['messages'][] = 'Supplied credential is invalid.';

            return $this->createAuthenticationResult();
        }

        $this->authenticationResultInfo['code'] = AuthenticationResult::SUCCESS;
        $this->authenticationResultInfo['identity'] = $identity;
        $this->authenticationResultInfo['messages'][] = 'Authentication successful.';

        return $this->createAuthenticationResult();
    }

    /**
     * This method abstracts the steps involved with making sure that this adapter was
     * indeed setup properly with all required pieces of information.
     *
     * @throws Exception\RuntimeException - in the event that setup was not done properly
     */
    protected function setupCredential()
    {
        if (null === $this->credential) {
            throw new Exception\RuntimeException(
                'A credential value was not provided prior to authentication with CredentialRepository'
                . ' authentication adapter'
            );
        }

        $this->authenticationResultInfo = [
            'code' => AuthenticationResult::FAILURE,
            'identity' => $this->identity,
            'messages' => []
        ];
    }

    /**
     * This method attempts to validate that the record in the resultset is indeed a
     * record that matched the identity provided to this adapter.
     *
     * @throws Exception\UnexpectedValueException
     * @param object $credential
     * @return AuthenticationResult
     */
    protected function validateCredential($credential)
    {
        $credentialIdentityProperty = $this->options->getCredentialIdentityProperty();
        $getter = 'get' . ucfirst($credentialIdentityProperty);
        $identity = null;

        if (method_exists($credential, $getter)) {
            $identity = $credential->$getter();
        } elseif (property_exists($credential, $credentialIdentityProperty)) {
            $identity = $credential->{$credentialIdentityProperty};
        } else {
            throw new Exception\UnexpectedValueException(
                sprintf(
                    'Property (%s) in (%s) is not accessible. You should implement %s::%s()',
                    $credentialIdentityProperty,
                    get_class($credential),
                    get_class($credential),
                    $getter
                )
            );
        }

        $callable = $this->options->getCredentialCallable();

        if ($callable) {
            $credentialValue = call_user_func($callable, $identity, $credential);
        } else {
            $credentialValue = true;
        }

        if ($credentialValue !== true) {
            $this->authenticationResultInfo['code'] = AuthenticationResult::FAILURE_CREDENTIAL_INVALID;
            $this->authenticationResultInfo['messages'][] = 'Supplied credential is invalid.';

            return $this->createAuthenticationResult();
        }

        $this->authenticationResultInfo['code'] = AuthenticationResult::SUCCESS;
        $this->authenticationResultInfo['identity'] = $identity;
        $this->authenticationResultInfo['messages'][] = 'Authentication successful.';

        return $this->createAuthenticationResult();
    }
}
