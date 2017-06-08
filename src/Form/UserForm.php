<?php
/**
 * @link      http://github.com/zetta-repo/zend-authentication for the canonical source repository
 * @copyright Copyright (c) 2017 Zetta Code
 */

namespace Zetta\ZendAuthentication\Form;

use Doctrine\ORM\EntityManagerInterface;
use Zend\Form\Form;
use Zetta\ZendAuthentication\InputFilter\UserFilter;
use Zetta\ZendAuthentication\Form\Fieldset\UserFieldset;

class UserForm extends Form
{

    /**
     * UserForm constructor.
     * @param EntityManagerInterface $em
     * @param string $name
     * @param array $options
     */
    public function __construct(EntityManagerInterface $em, $name = 'user', $options = [])
    {
        parent::__construct($name, $options);
        $this->setAttribute('method', 'post');
        $this->setAttribute('role', 'form');
        $inputFilter = new UserFilter($em, $options);
        $inputFilter->init();
        $this->setInputFilter($inputFilter);

        $userFieldser = new UserFieldset($em);
        $userFieldser->setUseAsBaseFieldset(true);

        $this->add($userFieldser);

        $this->add([
            'name' => 'submit-btn',
            'type' => 'Submit',
            'attributes' => [
                'class' => 'btn btn-primary',
                'value' => _('Submit'),
                'id' => $name . '-submit',
            ],
        ]);
    }
}