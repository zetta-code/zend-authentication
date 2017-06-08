<?php
/**
 * @link      http://github.com/zetta-repo/zend-authentication for the canonical source repository
 * @copyright Copyright (c) 2017 Zetta Code
 */

namespace Zetta\ZendAuthentication\InputFilter;

use Zend\InputFilter\InputFilter;
use Zend\Validator\EmailAddress;

class RecoverFilter extends InputFilter
{
    /**
     * RecoverPasswordFilter constructor.
     * @param array $options
     */
    public function __construct($options = [])
    {
        $this->add([
            'name' => 'email',
            'required' => true,
            'filters' => [
                ['name' => 'StripTags'],
                ['name' => 'StringTrim'],

            ],
            'validators' => [
                [
                    'name' => EmailAddress::class,
                    'options' => [
                        'message' => _('Invalid email address')
                    ],
                ],
            ],
        ]);
    }
}