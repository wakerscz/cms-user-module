<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\UserModule\Component\Admin\LoginForm;


interface ILoginForm
{
    /**
     * @return LoginForm
     */
    public function create() : LoginForm;
}