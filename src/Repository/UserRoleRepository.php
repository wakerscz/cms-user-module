<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\UserModule\Repository;


use Nette\InvalidArgumentException;
use Wakers\UserModule\Database\User;
use Wakers\UserModule\Database\UserRole;
use Wakers\UserModule\Database\UserRoleQuery;
use Wakers\UserModule\Security\UserAuthorizator;


class UserRoleRepository
{
    /**
     * @param User $user
     * @return array
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function findByUser_asArray(User $user) : array
    {
        $roles = [];

        foreach ($user->getUserRoles() as $role)
        {
            $roles[] = UserAuthorizator::ALL_ROLE_KEYS[$role->getRoleKey()];
        }

        return $roles;
    }


    /**
     * @param User $user
     * @param string $role
     * @return UserRole|null
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function findOneByRole(User $user, string $role) : ?UserRole
    {
        $key = array_search($role, UserAuthorizator::ALL_ROLE_KEYS);

        if ($key === NULL)
        {
            $class = UserAuthorizator::class;

            throw new InvalidArgumentException("Role {$role} is not defined in class {$class}");
        }

        $userRole = UserRoleQuery::create()
            ->filterByUser($user)
            ->findOneByRoleKey($key);

        return $userRole;
    }


    /**
     * @param User $user
     * @return mixed
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function findOneByUser(User $user)
    {
        return UserRoleQuery::create()
            ->filterByUser($user)
            ->find()
            ->getFirst();
    }
}