<?php
/**
 * Created by PhpStorm.
 * User: thiag
 * Date: 03/05/2018
 * Time: 10:15
 */

namespace Zetta\ZendAuthentication\Entity\Enum;


class Gender extends \SplEnum
{
    const __default = self::FEMALE;

    const FEMALE = 1;
    const MALE = 2;
}