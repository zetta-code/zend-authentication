<?php
/**
 * @link      http://github.com/zetta-repo/zend-authentication for the canonical source repository
 * @copyright Copyright (c) 2017 Zetta Code
 */

namespace Zetta\ZendAuthentication\Form\Fieldset;

use Application\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use Zend\Form\Fieldset;
use Zend\Hydrator\ClassMethods;
use Zetta\ZendAuthentication\Entity\AbstractUser;
use Zetta\ZendBootstrap\Hydrator\Strategy\DateStrategy;

class UserFieldset extends Fieldset
{
    /**
     * UserFieldset constructor.
     * @param EntityManagerInterface $em
     * @param string $name
     * @param array $options
     */
    public function __construct(EntityManagerInterface $em, $name = 'user', $options = [])
    {
        parent::__construct($name, $options);

        $hidrator = new ClassMethods(false);
        $this->setHydrator($hidrator);

        $this->add([
            'name' => 'id',
            'type' => 'hidden',
        ]);

        $this->add([
            'name' => 'name',
            'type' => 'text',
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => _('Name'),
            ],
            'options' => [
                'label' => _('Name'),
                'label_attributes' => ['class' => 'control-label'],
                'div' => ['class' => 'form-group', 'class_error' => 'has-error'],
            ],
        ]);

        $this->add([
            'name' => 'username',
            'type' => 'text',
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => _('Username'),
            ],
            'options' => [
                'label' => _('Username'),
                'label_attributes' => ['class' => 'control-label'],
                'div' => ['class' => 'form-group', 'class_error' => 'has-error'],
            ],
        ]);

        $this->add([
            'name' => 'email',
            'type' => 'text',
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => _('Email'),
            ],
            'options' => [
                'label' => _('Email'),
                'label_attributes' => ['class' => 'control-label'],
                'div' => ['class' => 'form-group', 'class_error' => 'has-error'],
            ],
        ]);

        $this->add([
            'name' => 'password',
            'type' => 'password',
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => _('Password'),
            ],
            'options' => [
                'label' => 'Password',
                'label_attributes' => ['class' => 'control-label'],
                'div' => ['class' => 'form-group', 'class_error' => 'has-error'],
            ],
        ]);

        $this->add([
            'name' => 'avatar',
            'type' => 'file',
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => _('Avatar'),
            ],
            'options' => [
                'label' => _('Avatar'),
                'label_attributes' => ['class' => 'control-label'],
                'div' => ['class' => 'form-group', 'class_error' => 'has-error'],
            ],
        ]);
    }
}
