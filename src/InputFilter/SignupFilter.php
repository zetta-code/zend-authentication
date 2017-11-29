<?php
/**
 * @link      http://github.com/zetta-repo/zend-authentication for the canonical source repository
 * @copyright Copyright (c) 2017 Zetta Code
 */

namespace Zetta\ZendAuthentication\InputFilter;

use Doctrine\ORM\EntityManagerInterface;
use DoctrineModule\Validator\UniqueObject;
use Zend\InputFilter\InputFilter;
use Zend\Validator\Identical;

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
                ['name' => 'Int'],
            ],
        ]);

        $this->add([
            'name' => 'username',
            'required' => true,
            'filters' => [
                ['name' => 'StripTags'],
                ['name' => 'StringTrim'],
            ],
            'validators' => [
                [
                    'name' => 'StringLength',
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min' => 5,
                        'max' => 100,
                    ],
                ],
                [
                    'name' => 'DoctrineModule\Validator\UniqueObject',
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
                ['name' => 'StripTags'],
                ['name' => 'StringTrim'],

            ],
            'validators' => [
                [
                    'name' => 'EmailAddress',
                    'options' => [
                        'message' => _('Invalid email address')
                    ],
                ],
                [
                    'name' => 'DoctrineModule\Validator\UniqueObject',
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
                ['name' => 'StringTrim'],
                ['name' => 'StringTrim']
            ],
            'validators' => [
                [
                    'name' => 'StringLength',
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min' => 6,
                        'max' => 128,
                    ],
                ],
            ],
        ]);

        $this->add([
            'name' => 'accepted-terms',
            'validators' => [
                [
                    'name' => 'Identical',
                    'options' => [
                        'token' => '1',
                        'literal' => TRUE,
                        'messages' => [
                            Identical::NOT_SAME => _('You must agree to the terms of use.')
                        ]
                    ],
                ],
            ],
        ]);
    }
}
