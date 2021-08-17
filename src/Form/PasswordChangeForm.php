<?php

/**
 * @link      https://github.com/zetta-code/zend-authentication for the canonical source repository
 * @copyright Copyright (c) 2018 Zetta Code
 */

declare(strict_types=1);

namespace Zetta\ZendAuthentication\Form;

use Laminas\Form\Element;
use Laminas\Form\Form;
use Zetta\ZendAuthentication\InputFilter\PasswordChangeFilter;

/**
 * Class PasswordChangeForm.
 * @method PasswordChangeFilter getInputFilter()
 */
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
            'name' => 'password-old',
            'type' => Element\Password::class,
            'attributes' => [
                'id' => $name . '-password-old',
                'class' => 'form-control',
                'placeholder' => _('Current Password'),
            ],
            'options' => [
                'label' => _('Current Password'),
                'div' => ['class' => 'form-group'],
            ],
        ]);

        $this->add([
            'name' => 'password-new',
            'type' => Element\Password::class,
            'attributes' => [
                'id' => $name . '-password-new',
                'class' => 'form-control',
                'placeholder' => _('New Password'),
            ],
            'options' => [
                'label' => _('New Password'),
                'div' => ['class' => 'form-group'],
            ],
        ]);

        $this->add([
            'name' => 'submit-btn',
            'type' => Element\Submit::class,
            'attributes' => [
                'class' => 'btn btn-primary',
                'value' => _('Change Password'),
                'id' => $name . '-submit',
            ],
        ]);
    }
}
