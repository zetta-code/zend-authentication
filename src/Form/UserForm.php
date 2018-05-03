<?php
/**
 * @link      http://github.com/zetta-code/zend-authentication for the canonical source repository
 * @copyright Copyright (c) 2018 Zetta Code
 */

namespace Zetta\ZendAuthentication\Form;

use Doctrine\ORM\EntityManagerInterface;
use Zend\Form\Form;
use Zetta\ZendAuthentication\Form\Fieldset\UserFieldset;
use Zetta\ZendAuthentication\InputFilter\UserFilter;

/**
 * Class UserForm
 * @method UserFilter getInputFilter()
 */
class UserForm extends Form
{
    /**
     * UserForm constructor.
     * @param EntityManagerInterface $entityManager
     * @param string $name
     * @param array $options
     */
    public function __construct(EntityManagerInterface $entityManager, $name = 'user', $options = [])
    {
        parent::__construct($name, $options);
        $this->setAttribute('method', 'post');
        $this->setAttribute('role', 'form');
        $this->setAttribute('novalidate', true);
        $inputFilter = new UserFilter($entityManager, $name, $options);
        $inputFilter->init();
        $this->setInputFilter($inputFilter);

        $userFieldser = new UserFieldset($entityManager, $name, $options);
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
