<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\UserModule\Database;


use Nette\InvalidArgumentException;
use Wakers\UserModule\Database\Base\UserRole as BaseUserRole;
use Wakers\UserModule\Security\UserAuthorizator;


class UserRole extends BaseUserRole
{
    public function setRole(string $v) : UserRole
    {
        $key = array_search($v, UserAuthorizator::ALL_ROLE_KEYS);

        if ($key === FALSE)
        {
            $class = UserAuthorizator::class;

            throw new InvalidArgumentException("Role {$v} is not defined in class {$class}");
        }

        return parent::setRoleKey($key);
    }


    /**
     * @return string
     */
    public function getRole() : string
    {
        $role = UserAuthorizator::ALL_ROLE_KEYS[$this->getRoleKey()];

        return $role;
    }
}