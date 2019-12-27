<?php
/**
 * @link      http://github.com/zetta-code/zend-authentication for the canonical source repository
 * @copyright Copyright (c) 2018 Zetta Code
 */

declare(strict_types=1);

namespace Zetta\ZendAuthentication\Form;

use Zend\Form\Element;
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
            'type' => Element\Text::class,
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => _('Email'),
            ],
            'options' => [
                'label' => 'Email',
                'div' => ['class' => 'form-group'],
            ],
        ]);


        $this->add([
            'name' => 'submit-btn',
            'type' => Element\Submit::class,
            'attributes' => [
                'class' => 'btn btn-lg btn-block btn-primary',
                'value' => _('Recover password'),
                'onclick' => 'return checkReCaptcha();',
                'id' => $name . '-submit',
            ],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->add([
            'type' => 'recaptcha',
            'name' => 'captcha',
            'options' => [
                'label' => _('Please verify you are human'),
            ],
        ]);
    }
}
