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

class UserFilter extends InputFilter
{
    const VALIDATION_PROFILE = [
        'user' => ['id', 'name', 'email', 'avatar']
    ];

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var InputFilter
     */
    protected $user;

    /**
     * @var string
     */
    protected $identityClass;

    /**
     * @var string
     */
    protected $identityProperty;

    /**
     * @var string
     */
    protected $emailProperty;

    /**
     * UserFilter constructor.
     * @param EntityManagerInterface $entityManager
     * @param array $options
     */
    public function __construct($entityManager, $options = [])
    {
        $this->entityManager = $entityManager;

        if (isset($options['identityClass'])) {
            $this->identityClass = $options['identityClass'];
        }

        if (isset($options['identityProperty'])) {
            $this->identityProperty = $options['identityProperty'];
        }

        if (isset($options['emailProperty'])) {
            $this->emailProperty = $options['emailProperty'];
        }
    }

    /**
     * @inheritDoc
     */
    public function init()
    {
        $this->inputs = [];

        $this->user = new InputFilter();
        $this->user->add([
            'name' => 'id',
            'required' => true,
            'filters' => [['name' => Filter\ToInt::class]]
        ]);

        $this->user->add([
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
                        'object_manager' => $this->entityManager,
                        'object_repository' => $this->entityManager->getRepository($this->identityClass),
                        'fields' => $this->identityProperty,
                        'messages' => [
                            UniqueObject::ERROR_OBJECT_NOT_UNIQUE => sprintf(_('The username %s already exists'), '\'%value%\'')
                        ]
                    ],
                ],
            ],
        ]);

        $this->user->add([
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
                        'message' => _('Invalid email address'),
                    ],
                ],
                [
                    'name' => 'DoctrineModule\Validator\UniqueObject',
                    'options' => [
                        'use_context' => true,
                        'object_manager' => $this->entityManager,
                        'object_repository' => $this->entityManager->getRepository($this->identityClass),
                        'fields' => $this->emailProperty,
                        'messages' => [
                            UniqueObject::ERROR_OBJECT_NOT_UNIQUE => sprintf(_('The email %s already exists'), '\'%value%\'')
                        ]
                    ],
                ],
            ],
        ]);

        $this->user->add([
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
                ]
            ]
        ]);

        $this->user->add([
            'name' => 'name',
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
                        'max' => 256,
                    ],
                ],
            ],
        ]);

        $this->user->add([
            'name' => 'avatar',
            'required' => false,
            'filters' => [
                [
                    'name' => Filter\File\RenameUpload::class,
                    'options' => [
                        'target' => './public/uploads/.png',
                        'randomize' => true,
                        'use_upload_extension ' => true
                    ]
                ]
            ],
            'validators' => [
                [
                    'name' => Validator\File\UploadFile::class
                ]
            ]
        ]);

        $this->add($this->user, 'user');
    }
}
