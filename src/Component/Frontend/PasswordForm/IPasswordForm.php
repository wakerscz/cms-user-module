<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\UserModule\Component\Frontend\PasswordForm;


use Wakers\UserModule\Database\User;


interface IPasswordForm
{
    /**
     * @param User $userEntity
     * @return PasswordForm
     */
    public function create(User $userEntity) : PasswordForm;
}