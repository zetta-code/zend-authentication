<?php
/**
 * @link      http://github.com/zetta-code/zend-authentication for the canonical source repository
 * @copyright Copyright (c) 2018 Zetta Code
 */

declare(strict_types=1);

namespace Zetta\ZendAuthentication\Form\Fieldset;

use Doctrine\ORM\EntityManagerInterface;
use DoctrineModule\Form\Element\ObjectSelect;
use Zend\Form\Element;
use Zend\Form\Fieldset;
use Zetta\DoctrineUtil\Hydrator\DoctrineObject;
use Zetta\ZendAuthentication\Entity\Enum\Gender;
use Zetta\ZendBootstrap\Hydrator\Strategy\DateStrategy;

class UserFieldset extends Fieldset
{
    /**
     * UserFieldset constructor.
     * @param EntityManagerInterface $entityManager
     * @param string $name
     * @param array $options
     */
    public function __construct(EntityManagerInterface $entityManager, $name = 'user', $options = [])
    {
        parent::__construct($name, $options);

        if (isset($options['identityClass'])) {
            $identityClass = $options['identityClass'];
        } else {
            $identityClass = '';
        }
        if (isset($options['roleClass'])) {
            $roleClass = $options['roleClass'];
        } else {
            $roleClass = '';
        }

        $hidrator = new DoctrineObject($entityManager);
        $hidrator->addStrategy('birthday', new DateStrategy());
        $this->setHydrator($hidrator);
        $this->setObject(new $identityClass());

        $this->add([
            'name' => 'id',
            'type' => Element\Hidden::class,
        ]);

        $this->add([
            'name' => 'role',
            'type' => ObjectSelect::class,
            'attributes' => [
                'class' => 'form-control selectpicker',
                'data-container' => 'body',
                'data-live-search' => 'true',
                'required' => false,
            ],
            'options' => [
                'label' => _('Role'),
                'div' => ['class' => 'form-group'],
                'object_manager' => $entityManager,
                'target_class' => $roleClass,
                'property' => 'name',
                'is_method' => true,
                'empty_option' => _('Select'),
                'find_method' => [
                    'name' => 'findBy',
                    'params' => [
                        'criteria' => [
                            'deletedAt' => null
                        ],
                        'orderBy' => ['name' => 'ASC'],
                    ],
                ],
            ],
        ]);

        $this->add([
            'name' => 'name',
            'type' => Element\Text::class,
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => _('Name'),
            ],
            'options' => [
                'label' => _('Name'),
                'div' => ['class' => 'form-group'],
            ],
        ]);

        $this->add([
            'name' => 'username',
            'type' => Element\Text::class,
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => _('Username'),
            ],
            'options' => [
                'label' => _('Username'),
                'div' => ['class' => 'form-group'],
            ],
        ]);

        $this->add([
            'name' => 'email',
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
                'id' => $name . '-password',
                'class' => 'form-control',
                'placeholder' => _('Password'),
            ],
            'options' => [
                'label' => 'Password',
                'div' => ['class' => 'form-group'],
            ],
        ]);

        $this->add([
            'name' => 'avatar',
            'type' => Element\File::class,
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => _('Avatar'),
            ],
            'options' => [
                'label' => _('Avatar'),
                'div' => ['class' => 'form-group'],
            ],
        ]);

        $this->add([
            'name' => 'gender',
            'type' => Element\Radio::class,
            'attributes' => [
                'class' => 'custom-control-input',
                'placeholder' => _('Gender'),
            ],
            'options' => [
                'label' => _('Gender'),
                'label_attributes' => [
                    'class' => 'custom-control-label'
                ],
                'div' => ['class' => 'custom-control custom-radio custom-control-inline'],
                'value_options' => [
                    [
                        'value' => Gender::FEMALE,
                        'label' => _('Female'),
                        'attributes' => [
                            'id' => $name . '-gender-' . Gender::FEMALE,
                        ],
                    ],
                    [
                        'value' => Gender::MALE,
                        'label' => _('Male'),
                        'attributes' => [
                            'id' => $name . '-gender-' . Gender::MALE,
                        ],
                    ],
                ]
            ],
        ]);

        $this->add([
            'name' => 'birthday',
            'type' => Element\Text::class,
            'attributes' => [
                'class' => 'form-control mask-date',
                'placeholder' => _('Birthday'),
            ],
            'options' => [
                'label' => _('Birthday'),
                'div' => ['class' => 'form-group'],
            ],
        ]);

        $this->add([
            'name' => 'bio',
            'type' => Element\Textarea::class,
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => _('Bio'),
            ],
            'options' => [
                'label' => _('Bio'),
                'div' => ['class' => 'form-group'],
            ],
        ]);
    }
}
