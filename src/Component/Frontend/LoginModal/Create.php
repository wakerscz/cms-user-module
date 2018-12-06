<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\UserModule\Component\Frontend\LoginModal;


trait Create
{
    /**
     * @var ILoginModal
     * @inject
     */
    public $IUser_LoginModal;


    /**
     * Modální okno pro přihlášení uživatele na frontendu
     * @return LoginModal
     */
    protected function createComponentUserLoginModal() : object
    {
        return $this->IUser_LoginModal->create();
    }
}