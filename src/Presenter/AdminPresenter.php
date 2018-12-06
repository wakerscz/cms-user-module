<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\UserModule\Presenter;


use Wakers\BaseModule\Presenter\BaseAdminPresenter;
use Wakers\BaseModule\Security\BaseAuthorizator;


class AdminPresenter extends BaseAdminPresenter
{
    use \Wakers\UserModule\Component\Admin\LoginForm\Create;

    /**
     * @throws \Nette\Application\AbortException
     */
    public function actionLogin()
    {
        if ($this->user->isAllowed(BaseAuthorizator::RES_SITE_MANAGER))
        {
            $this->redirect(':Base:Admin:Dashboard');
        }
    }
}