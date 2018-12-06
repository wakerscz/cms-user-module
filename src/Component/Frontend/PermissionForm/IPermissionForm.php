<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\UserModule\Component\Frontend\PermissionForm;


use Wakers\UserModule\Database\User;


interface IPermissionForm
{
    /**
     * @param User $userEntity
     * @return PermissionForm
     */
    public function create(User $userEntity) : PermissionForm;
}