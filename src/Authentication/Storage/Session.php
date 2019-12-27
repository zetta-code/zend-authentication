<?php
/**
 * @link      http://github.com/zetta-code/zend-authentication for the canonical source repository
 * @copyright Copyright (c) 2018 Zetta Code
 */

declare(strict_types=1);

namespace Zetta\ZendAuthentication\Authentication\Storage;

use DoctrineModule\Options\Authentication;
use Zend\Session\ManagerInterface as SessionManager;
use Zetta\ZendAuthentication\Options\Authentication as AuthenticationOptions;

/**
 * Authentication storage that uses a Doctrine object for verification.
 */
class Session extends \Zend\Authentication\Storage\Session
{
    /**
     *
     * @var AuthenticationOptions
     */
    protected $options;

    /**
     * Sets session storage options and initializes session namespace object
     *
     * @param mixed $namespace
     * @param mixed $member
     * @param SessionManager $manager
     * @param array | Authentication $options
     */
    public function __construct($namespace = null, $member = null, SessionManager $manager = null, $options = [])
    {
        parent::__construct($namespace, $member, $manager);
        $this->setOptions($options);
    }

    /**
     * @param array | AuthenticationOptions $options
     * @return Session
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
     * This function assumes that the storage only contains identifier values (which is the case if
     * the ObjectRepository authentication adapter is used).
     *
     * @return null|object
     */
    public function read()
    {
        if (($identity = parent::read())) {

            return $this->options->getObjectRepository()->find($identity);
        }

        return null;
    }

    /**
     * Will return the key of the identity. If only the key is needed, this avoids an
     * unnecessary db call
     *
     * @return mixed
     */
    public function readKeyOnly()
    {
        return $identity = parent::read();
    }

    /**
     * @param object $identity
     * @return void
     */
    public function write($identity)
    {
        $metadataInfo = $this->options->getClassMetadata();
        $identifierValues = $metadataInfo->getIdentifierValues($identity);

        parent::write($identifierValues);
    }

    /**
     * @param bool $rememberMe
     * @param int $time
     * @return void
     */
    public function setRememberMe($rememberMe = false, $time = 1209600)
    {
        if ($rememberMe) {
            $this->session->getManager()->rememberMe($time);
        }
    }

    /**
     * Forget me
     * @return void
     */
    public function forgetMe()
    {
        $this->session->getManager()->forgetMe();
    }

    /**
     * Expire session cookie
     * @return void
     */
    public function expireSessionCookie()
    {
        $this->session->getManager()->expireSessionCookie();
    }
}
