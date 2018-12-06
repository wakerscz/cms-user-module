<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\UserModule\Repository;


use Propel\Runtime\Collection\ObjectCollection;
use Wakers\UserModule\Database\User;
use Wakers\UserModule\Database\UserQuery;


class UserRepository
{
    /**
     * @param string $email
     * @return User|NULL
     */
    public function findOneByEmail(string $email) : ?User
    {
        return UserQuery::create()
            ->findOneByEmail($email);
    }

    /**
     * @param string $email
     * @return User|NULL
     */
    public function findOneByEmailJoinRoles(string $email) : ?User
    {
        return UserQuery::create()
            ->joinWithUserRole()
            ->filterByEmail($email)
            ->find()
            ->getFirst();
    }


    /**
     * @param int $id
     * @return User|NULL
     */
    public function findOneById(int $id) : ?User
    {
        return UserQuery::create()
            ->findOneById($id);
    }


    /**
     * @param int $id
     * @return User|NULL
     */
    public function findOneByIdJoinRoles(int $id) : ?User
    {
        return UserQuery::create()
            ->joinWithUserRole()
            ->filterById($id)
            ->find()
            ->getFirst();
    }


    /**
     * @return ObjectCollection|User[]
     */
    public function findAll() : ObjectCollection
    {
        return UserQuery::create()
            ->find();
    }


    /**
     * @return ObjectCollection|User[]
     */
    public function findAllJoinRoles() : ObjectCollection
    {
        return UserQuery::create()
            ->joinWithUserRole()
            ->find();
    }
}