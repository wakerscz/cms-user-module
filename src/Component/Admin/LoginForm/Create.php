<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\UserModule\Component\Admin\LoginForm;


trait Create
{
    /**
     * @var ILoginForm
     * @inject
     */
    public $IUser_LoginForm;


    /**
     * Přihlašovací formulář
     * @return LoginForm
     */
    protected function createComponentUserLoginForm() : object
    {
        return $this->IUser_LoginForm->create();
    }
}