<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\UserModule\Component\Frontend\EditModal;


trait Create
{
    /**
     * @var IEditModal
     * @inject
     */
    public $IUser_EditModal;


    /**
     * Modální okno pro úpravu uživatele
     * @return EditModal
     */
    protected function createComponentUserEditModal() : object
    {
        $control = $this->IUser_EditModal->create();

        $control->onSave[] = function ()
        {
            $this->getComponent('userSummaryModal')->redrawControl('users');
        };

        $control->onOpen[] = function () use ($control)
        {
            $control->redrawControl('modal');
        };

        $control->onSavePassword[] = function () use ($control)
        {
            $control->getComponent('passwordForm')->redrawControl('passwordForm');
        };

        return $control;
    }
}