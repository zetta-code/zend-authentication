<?php
/**
 * @link      http://github.com/zetta-repo/zend-authentication for the canonical source repository
 * @copyright Copyright (c) 2017 Zetta Code
 */

namespace Zetta\ZendAuthentication\Form;

use Doctrine\ORM\EntityManagerInterface;
use Zend\Form\Form;
use Zend\Hydrator\ClassMethods;
use Zetta\ZendAuthentication\InputFilter\SignupFilter;

class SignupForm extends Form
{

    /**
     * SignupForm constructor.
     * @param EntityManagerInterface $em
     * @param string $name
     * @param array $options
     */
    public function __construct(EntityManagerInterface $em, $name = 'signup', $options = [])
    {
        parent::__construct($name, $options);
        $this->setAttribute('method', 'post');
        $this->setAttribute('role', 'form');
        $this->setHydrator(new ClassMethods(false));
        $this->setInputFilter(new SignupFilter($em, $options));

        $this->add([
            'name' => 'id',
            'type' => 'hidden',
        ]);

        $this->add([
            'name' => 'username',
            'type' => 'text',
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => _('Username'),
            ],
            'options' => [
                'label' => 'Username',
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
                'label' => 'Email',
                'label_attributes' => ['class' => 'control-label'],
                'div' => ['class' => 'form-group', 'class_error' => 'has-error'],
            ],
        ]);

        $this->add([
            'name' => 'password',
            'type' => 'password',
            'attributes' => [
                'id' => $name . '-password',
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
            'name' => 'accepted-terms',
            'type' => 'checkbox',
            'attributes' => [
                'class' => 'custom-control-input'
            ],
            'options' => [
                'label' => _('I have read and accepted the terms of use.'),
                'label_attributes' => ['class' => 'custom-control custom-checkbox'],
                'label_options' => ['always_wrap' => true, 'span_class' => 'custom-control-label'],
                'div' => ['class' => 'form-group', 'class_error' => 'has-error'],
            ],
        ]);

        $this->add([
            'name' => 'submit-btn',
            'type' => 'submit',
            'attributes' => [
                'class' => 'btn btn-lg btn-block btn-primary',
                'value' => _('Sign me up'),
                'id' => $name . '-submit',
            ],
        ]);
    }
}
