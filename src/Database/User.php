<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\UserModule\Database;


use Nette\InvalidArgumentException;
use Wakers\BaseModule\Util\Validator;
use Wakers\UserModule\Database\Base\User as BaseUser;
use Wakers\UserModule\Security\Passwords;
use Wakers\UserModule\Security\UserAuthorizator;


class User extends BaseUser
{
    /**
     * @param string $v
     * @return $this|User
     */
    public function setPassword($v) : User
    {
        $hash = Passwords::hash($v);

        return parent::setPassword($hash);
    }


    /**
     * @param string $v
     * @return $this|User
     */
    public function setEmail($v) : User
    {
        if (!Validator::isEmail($v))
        {
            throw new InvalidArgumentException("E-mail {$v} is not valid e-mail");
        }

        return parent::setEmail($v);
    }


    /**
     * @param int $v
     * @return User
     */
    public function setStatus($v) : User
    {
        $key = array_search($v, UserAuthorizator::ALL_STATUS_KEYS);

        if ($key === FALSE)
        {
            $class = UserAuthorizator::class;

            throw new InvalidArgumentException("Status {$v} is not defined in class {$class}");
        }

        return parent::setStatus($key);
    }


    /**
     * @param string $role
     * @return bool
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function isInRole(string $role) : bool
    {
        foreach ($this->getUserRoles() as $userRole)
        {
            if ($userRole->getRole() === $role)
            {
               return TRUE;
            }
        }

        return FALSE;
    }

}