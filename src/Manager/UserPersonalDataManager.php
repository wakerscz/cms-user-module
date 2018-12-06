<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\UserModule\Manager;


use Propel\Runtime\Exception\PropelException;
use Wakers\BaseModule\Database\AbstractDatabase;
use Wakers\UserModule\Database\User;
use Wakers\UserModule\Database\UserPersonalData;


class UserPersonalDataManager extends AbstractDatabase
{
    /**
     * @param User $user
     * @param string $firstName
     * @param string $lastName
     * @throws PropelException
     */
    public function saveName(User $user, string $firstName = NULL, string $lastName = NULL) : void
    {
        $userPersonalData = $user->getUserPersonalData();

        if ($userPersonalData === NULL)
        {
            $userPersonalData = new UserPersonalData;
            $userPersonalData->setUser($user);
        }

        $userPersonalData->setFirstName($firstName);
        $userPersonalData->setLastName($lastName);
        $userPersonalData->save();
    }


    /**
     * @param User $user
     * @param string|null $phone
     * @throws PropelException
     */
    public function savePhone(User $user, string $phone = NULL) : void
    {
        $userPersonalData = $user->getUserPersonalData();

        if ($userPersonalData === NULL)
        {
            $userPersonalData = new UserPersonalData;
            $userPersonalData->setUser($user);
        }

        $userPersonalData->setPhone($phone);
        $userPersonalData->save();
    }
}