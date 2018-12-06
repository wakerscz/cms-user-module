<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\UserModule\Component\Frontend\LoginModal;


interface ILoginModal
{
    /**
     * @return LoginModal
     */
    public function create() : LoginModal;
}