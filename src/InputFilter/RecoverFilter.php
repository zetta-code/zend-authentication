<?php

/**
 * @link      https://github.com/zetta-code/zend-authentication for the canonical source repository
 * @copyright Copyright (c) 2018 Zetta Code
 */

declare(strict_types=1);

namespace Zetta\ZendAuthentication\InputFilter;

use Laminas\Filter;
use Laminas\InputFilter\InputFilter;
use Laminas\Validator\EmailAddress;

/**
 * Class RecoverFilter.
 */
class RecoverFilter extends InputFilter
{
    /**
     * RecoverPasswordFilter constructor.
     */
    public function __construct()
    {
        $this->add([
            'name' => 'email',
            'required' => true,
            'filters' => [
                ['name' => Filter\StripTags::class],
                ['name' => Filter\StringTrim::class],

            ],
            'validators' => [
                [
                    'name' => EmailAddress::class,
                    'options' => [
                        'message' => _('Invalid email address'),
                    ],
                ],
            ],
        ]);
    }
}
