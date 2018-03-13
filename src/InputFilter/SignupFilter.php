<?php
/**
 * @link      http://github.com/zetta-repo/zend-authentication for the canonical source repository
 * @copyright Copyright (c) 2017 Zetta Code
 */

namespace Zetta\ZendAuthentication\InputFilter;

use Doctrine\ORM\EntityManagerInterface;
use DoctrineModule\Validator\UniqueObject;
use Zend\Filter;
use Zend\InputFilter\InputFilter;
use Zend\Validator;

class SignupFilter extends InputFilter
{
    /**
     * SignupFilter constructor.
     * @param EntityManagerInterface $em
     * @param array $options
     */
    public function __construct(EntityManagerInterface $em, $options = [])
    {
        $this->add([
            'name' => 'id',
            'required' => true,
            'filters' => [
                ['name' => Filter\ToInt::class],
            ],
        ]);

        $this->add([
            'name' => 'username',
            'required' => true,
            'filters' => [
                ['name' => Filter\StripTags::class],
                ['name' => Filter\StringTrim::class],
            ],
            'validators' => [
                [
                    'name' => Validator\StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min' => 5,
                        'max' => 100,
                    ],
                ],
                [
                    'name' => UniqueObject::class,
                    'options' => [
                        'use_context' => true,
                        'object_manager' => $em,
                        'object_repository' => $em->getRepository($options['identityClass']),
                        'fields' => $options['identityProperty'],
                        'messages' => [
                            UniqueObject::ERROR_OBJECT_NOT_UNIQUE => sprintf(_('The username %s already exists'), '\'%value%\'')
                        ]
                    ],
                ],
            ],
        ]);

        $this->add([
            'name' => 'email',
            'required' => true,
            'filters' => [
                ['name' => Filter\StripTags::class],
                ['name' => Filter\StringTrim::class],

            ],
            'validators' => [
                [
                    'name' => Validator\EmailAddress::class,
                    'options' => [
                        'message' => _('Invalid email address')
                    ],
                ],
                [
                    'name' => UniqueObject::class,
                    'options' => [
                        'use_context' => true,
                        'object_manager' => $em,
                        'object_repository' => $em->getRepository($options['identityClass']),
                        'fields' => $options['emailProperty'],
                        'messages' => [
                            UniqueObject::ERROR_OBJECT_NOT_UNIQUE => sprintf(_('The email %s already exists'), '\'%value%\'')
                        ]
                    ],
                ],
            ],
        ]);

        $this->add([
            'name' => 'password',
            'required' => true,
            'filters' => [
                ['name' => Filter\StripTags::class],
                ['name' => Filter\StringTrim::class],
            ],
            'validators' => [
                [
                    'name' => Validator\StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min' => 6,
                        'max' => 128,
                    ],
                ]
            ]
        ]);

        $this->add([
            'name' => 'accepted-terms',
            'validators' => [
                [
                    'name' => Validator\Identical::class,
                    'options' => [
                        'token' => '1',
                        'literal' => true,
                        'messages' => [
                            Validator\Identical::NOT_SAME => _('You must agree to the terms of use.')
                        ]
                    ],
                ],
            ],
        ]);
    }
}
