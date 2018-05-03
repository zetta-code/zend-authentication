<?php
/**
 * @link      http://github.com/zetta-code/zend-authentication for the canonical source repository
 * @copyright Copyright (c) 2018 Zetta Code
 */

namespace Zetta\ZendAuthentication\InputFilter;

use Zend\Filter;
use Zend\InputFilter\InputFilter;
use Zend\Validator\StringLength;

class PasswordChangeFilter extends InputFilter
{
    /**
     * PasswordChangeFilter constructor.
     * @param array $options
     */
    public function __construct($options = [])
    {
        $this->add([
            'name' => 'password-old',
            'required' => true,
            'filters' => [
                ['name' => Filter\StripTags::class],
                ['name' => Filter\StringTrim::class],
            ],
        ]);

        $this->add([
            'name' => 'password-new',
            'required' => true,
            'filters' => [
                ['name' => Filter\StripTags::class],
                ['name' => Filter\StringTrim::class],
            ],
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min' => 5,
                        'max' => 16,
                    ],
                ],
            ],
        ]);
    }
}
