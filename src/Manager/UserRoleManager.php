<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\UserModule\Manager;


use Wakers\BaseModule\Database\AbstractDatabase;
use Wakers\UserModule\Database\User;
use Wakers\UserModule\Database\UserRole;
use Wakers\UserModule\Repository\UserRoleRepository;
use Wakers\UserModule\Security\UserAuthorizator;


class UserRoleManager extends AbstractDatabase
{
    /**
     * @var UserRoleRepository
     */
    protected $userRoleRepository;


    /**
     * UserRoleManager constructor.
     * @param UserRoleRepository $userRoleRepository
     */
    public function __construct(UserRoleRepository $userRoleRepository)
    {
        $this->userRoleRepository = $userRoleRepository;
    }


    /**
     * @param User $user
     * @param int $roleKey
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function updateRole(User $user, int $roleKey) : void
    {
        $userRole = $this->userRoleRepository->findOneByUser($user);
        $userRole->setRoleKey($roleKey);
        $userRole->save();
    }


    /**
     * @param User $user
     * @param string $role
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function addRole(User $user, string $role = UserAuthorizator::ROLE_EDITOR) : void
    {
        $userRole = new UserRole;
        $userRole->setRole($role);
        $userRole->setUser($user);
        $userRole->save();
    }


    /**
     * @param User $user
     * @param string $role
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function removeRole(User $user, string $role) : void
    {
        $userRole = $this->userRoleRepository->findOneByRole($user, $role);

        if ($userRole !== NULL)
        {
            $userRole->delete();
        }
    }
}