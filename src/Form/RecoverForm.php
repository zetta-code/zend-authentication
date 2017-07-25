<?php
/**
 * @link      http://github.com/zetta-repo/zend-authentication for the canonical source repository
 * @copyright Copyright (c) 2017 Zetta Code
 */

namespace Zetta\ZendAuthentication\Form;

use Zend\Form\Form;
use Zend\Hydrator\ClassMethods;
use Zetta\ZendAuthentication\InputFilter\RecoverFilter;

class RecoverForm extends Form
{

    /**
     * RecoverForm constructor.
     * @param string $name
     * @param array $options
     */
    public function __construct($name = 'signup', $options = [])
    {
        parent::__construct($name, $options);
        $this->setAttribute('method', 'post');
        $this->setAttribute('role', 'form');
        $this->setHydrator(new ClassMethods(false));
        $this->setInputFilter(new RecoverFilter());

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
            'name' => 'submit-btn',
            'type' => 'submit',
            'attributes' => [
                'class' => 'btn btn-lg btn-block btn-primary',
                'value' => _('Recover password'),
                'id' => $name . '-submit',
            ],
        ]);
    }
}
