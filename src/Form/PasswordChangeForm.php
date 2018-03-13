<?php
/**
 * @link      http://github.com/zetta-repo/zend-authentication for the canonical source repository
 * @copyright Copyright (c) 2017 Zetta Code
 */

namespace Zetta\ZendAuthentication\Form;

use Zend\Form\Form;
use Zetta\ZendAuthentication\InputFilter\PasswordChangeFilter;

class PasswordChangeForm extends Form
{

    /**
     * PasswordChangeForm constructor.
     * @param string $name
     * @param array $options
     */
    public function __construct($name = 'password-change', $options = [])
    {
        parent::__construct($name, $options);

        $this->setAttribute('method', 'post');
        $this->setAttribute('role', 'form');
        $this->setInputFilter(new PasswordChangeFilter($options));

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
            'name' => 'submit-btn',
            'type' => 'Submit',
            'attributes' => [
                'class' => 'btn btn-lg btn-block btn-primary',
                'value' => _('Change Password'),
                'id' => $name . '-submit',
            ],
        ]);
    }
}
