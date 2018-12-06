<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\UserModule\Database;


use Wakers\BaseModule\Util\Validator;
use Wakers\UserModule\Database\Base\UserPersonalData as BaseUserPersonalData;


class UserPersonalData extends BaseUserPersonalData
{
    /**
     * @param string $v
     * @return BaseUserPersonalData|UserPersonalData
     */
    public function setPhone($v)
    {
        $v = Validator::isStringEmpty($v) ? NULL : $v;

        return parent::setPhone($v);
    }


    /**
     * @param string $v
     * @return BaseUserPersonalData|UserPersonalData
     */
    public function setFirstName($v)
    {
        $v = Validator::isStringEmpty($v) ? NULL : $v;

        return parent::setFirstName($v);
    }


    /**
     * @param string $v
     * @return BaseUserPersonalData|UserPersonalData
     */
    public function setLastName($v)
    {
        $v = Validator::isStringEmpty($v) ? NULL : $v;

        return parent::setLastName($v);
    }

}
