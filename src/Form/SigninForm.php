<?php
/**
 * @link      http://github.com/zetta-code/zend-authentication for the canonical source repository
 * @copyright Copyright (c) 2018 Zetta Code
 */

declare(strict_types=1);

namespace Zetta\ZendAuthentication\Form;

use Zend\Form\Element;
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
            'type' => Element\Text::class,
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => _('Email'),
            ],
            'options' => [
                'label' => _('Email'),
                'div' => ['class' => 'form-group'],
            ],
        ]);

        $this->add([
            'name' => 'password',
            'type' => Element\Password::class,
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => _('Password'),
            ],
            'options' => [
                'label' => _('Password'),
                'div' => ['class' => 'form-group'],
            ],
        ]);

        $this->add([
            'name' => 'remember-me',
            'type' => Element\Checkbox::class,
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
            'type' => Element\Submit::class,
            'attributes' => [
                'class' => 'btn btn-lg btn-block btn-primary',
                'value' => _('Sign me in'),
                'id' => $name . '-submit',
            ],
        ]);
    }
}
