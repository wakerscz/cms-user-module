<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\UserModule\Component\Frontend\RemoveModal;


trait Create
{
    /**
     * @var IRemoveModal
     * @inject
     */
    public $IUser_RemoveModal;


    /**
     * Modální okno pro odstranění uživatele
     * @return RemoveModal
     */
    protected function createComponentUserRemoveModal() : object
    {
        $control = $this->IUser_RemoveModal->create();

        $control->onRemove[] = function ()
        {
            $this->getComponent('userSummaryModal')->redrawControl('users');
        };

        $control->onOpen[] = function () use ($control)
        {
            $control->redrawControl('modal');
        };

        return $control;
    }
}