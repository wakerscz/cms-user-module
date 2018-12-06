<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\UserModule\Component\Frontend\EditModal;


interface IEditModal
{
    /**
     * @return EditModal
     */
    public function create() : EditModal;
}