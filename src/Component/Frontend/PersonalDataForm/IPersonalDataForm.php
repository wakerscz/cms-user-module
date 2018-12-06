<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\UserModule\Component\Frontend\PersonalDataForm;


use Wakers\UserModule\Database\User;


interface IPersonalDataForm
{
    /**
     * @param User $userEntity
     * @return PersonalDataForm
     */
    public function create(User $userEntity) : PersonalDataForm;
}