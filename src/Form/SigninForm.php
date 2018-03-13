<?php
/**
 * @link      http://github.com/zetta-repo/zend-authentication for the canonical source repository
 * @copyright Copyright (c) 2017 Zetta Code
 */

namespace Zetta\ZendAuthentication\Form;

use Zend\Form\Form;
use Zetta\ZendAuthentication\InputFilter\SigninFilter;

class SigninForm extends Form
{

    /**
     * SigninForm constructor.
     * @param string $name
     * @param array $options
     */
    public function __construct($name = 'signin', $options = [])
    {
        parent::__construct($name, $options);
        $this->setAttribute('method', 'post');
        $this->setAttribute('role', 'form');
        $this->setInputFilter(new SigninFilter());

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
            'name' => 'password',
            'type' => 'password',
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => _('Password'),
            ],
            'options' => [
                'label' => _('Password'),
                'label_attributes' => ['class' => 'control-label'],
                'div' => ['class' => 'form-group', 'class_error' => 'has-error'],
            ],
        ]);

        $this->add([
            'name' => 'remember-me',
            'type' => 'checkbox',
            'attributes' => [
                'class' => 'custom-control-input'
            ],
            'options' => [
                'label' => _('Remember-me'),
                'label_attributes' => ['class' => 'custom-control custom-checkbox'],
                'label_options' => ['always_wrap' => true, 'span_class' => 'custom-control-label'],
            ],
        ]);

        $this->add([
            'name' => 'submit-btn',
            'type' => 'submit',
            'attributes' => [
                'class' => 'btn btn-lg btn-block btn-primary',
                'value' => _('Sign me in'),
                'id' => $name . '-submit',
            ],
        ]);
    }
}
