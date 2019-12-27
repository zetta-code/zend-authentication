<?php
/**
 * @link      http://github.com/zetta-code/zend-authentication for the canonical source repository
 * @copyright Copyright (c) 2018 Zetta Code
 */

declare(strict_types=1);

namespace Zetta\ZendAuthentication\Exception;

use BadMethodCallException;

/**
 * Exception to be thrown in case of unauthorized access to a resource
 */
class ResourceNotFoundException extends BadMethodCallException
{
}
