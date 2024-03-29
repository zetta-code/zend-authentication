<?php

/**
 * @link      https://github.com/zetta-code/zend-authentication for the canonical source repository
 * @copyright Copyright (c) 2018 Zetta Code
 */

declare(strict_types=1);

namespace Zetta\ZendAuthentication\InputFilter;

use Doctrine\ORM\EntityManagerInterface;
use DoctrineModule\Validator\UniqueObject;
use Laminas\Filter;
use Laminas\InputFilter\InputFilter;
use Laminas\Validator;

/**
 * Class UserFilter.
 */
class UserFilter extends InputFilter
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var string
     */
    protected $name;

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
     * @param string $name
     * @param array $options
     */
    public function __construct($entityManager, $name = 'user', $options = [])
    {
        $this->entityManager = $entityManager;
        $this->name = $name;

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
            'filters' => [['name' => Filter\ToInt::class]],
        ]);

        $this->user->add([
            'name' => 'role',
            'required' => true,
            'filters' => [['name' => Filter\ToInt::class]],
        ]);

        $this->user->add([
            'name' => 'username',
            'required' => false,
            'filters' => [
                ['name' => Filter\StripTags::class],
                ['name' => Filter\StringTrim::class],
                ['name' => Filter\ToNull::class],
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
                            UniqueObject::ERROR_OBJECT_NOT_UNIQUE => _('The username \'%value%\' already exists'),
                        ],
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
                            UniqueObject::ERROR_OBJECT_NOT_UNIQUE => _('The email \'%value%\' already exists'),
                        ],
                    ],
                ],
            ],
        ]);

        $this->user->add([
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
                ],
            ],
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
                        'min' => 2,
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
                        'target' => './public/uploads/avatar.png',
                        'randomize' => true,
                        'use_upload_extension ' => true,
                    ],
                ],
            ],
            'validators' => [
                [
                    'name' => Validator\File\UploadFile::class,
                ],
            ],
        ]);

        $this->user->add([
            'name' => 'gender',
            'required' => true,
            'filters' => [['name' => Filter\ToInt::class]],
        ]);

        $this->user->add([
            'name' => 'birthday',
            'required' => true,
            'filters' => [
                ['name' => Filter\StringTrim::class],
                ['name' => Filter\StripTags::class],
                ['name' => Filter\ToNull::class],

            ],
            'validators' => [
                [
                    'name' => Validator\Date::class,
                    'options' => [
                        'format' => 'd/m/Y',
                    ],
                ],
            ],
        ]);

        $this->user->add([
            'name' => 'bio',
            'required' => false,
            'filters' => [
                ['name' => Filter\StringTrim::class],
                ['name' => Filter\StripTags::class],
                ['name' => Filter\ToNull::class],
            ],
        ]);

        $this->add($this->user, $this->name);
    }

    public function getNewValidationGroup()
    {
        return [
            $this->name => ['id', 'role', 'username', 'email', 'password', 'name', 'avatar', 'gender', 'birthday', 'bio']
        ];
    }

    public function getProfileValidationGroup()
    {
        return [
            $this->name => ['id', 'username', 'email', 'name', 'avatar', 'gender', 'birthday', 'bio']
        ];
    }

    public function getSignupValidationGroup()
    {
        return [
            $this->name => ['id', 'email', 'password', 'name']
        ];
    }
}
