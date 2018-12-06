<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\UserModule\Component\Frontend\SummaryModal;


trait Create
{
    /**
     * @var ISummaryModal
     * @inject
     */
    public $IUser_SummaryModal;


    /**
     * Modální okno s přehledem všech uživatelů
     *
     * @return SummaryModal
     */
    protected function createComponentUserSummaryModal() : object
    {
        return $this->IUser_SummaryModal->create();
    }
}