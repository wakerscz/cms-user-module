<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\UserModule\Component\Frontend\AddModal;


trait Create
{
    /**
     * @var IAddModal
     * @inject
     */
    public $IUser_AddModal;


    /**
     * Modální okno pro vytvoření uživatele
     * @return AddModal
     */
    protected function createComponentUserAddModal() : object
    {
        $control = $this->IUser_AddModal->create();

        $control->onSave[] = function () use ($control)
        {
            $this->getComponent('userSummaryModal')->redrawControl('users');
            $control->redrawControl('addForm');
        };

        return $control;
    }
}