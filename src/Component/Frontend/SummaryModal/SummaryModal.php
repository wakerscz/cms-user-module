<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\UserModule\Component\Frontend\SummaryModal;


use Wakers\BaseModule\Component\Admin\BaseControl;
use Wakers\UserModule\Manager\UserManager;
use Wakers\UserModule\Repository\UserRepository;


class SummaryModal extends BaseControl
{
    /**
     * @var UserRepository
     */
    private $userRepository;


    /**
     * @var UserManager
     */
    private $userManager;


    /**
     * UserSummaryModal constructor.
     * @param UserRepository $userRepository
     * @param UserManager $userManager
     */
    public function __construct(UserRepository $userRepository, UserManager $userManager)
    {
        $this->userRepository = $userRepository;
        $this->userManager = $userManager;
    }


    /**
     * Render
     */
    public function render() : void
    {
        $this->template->userEntities = $this->userRepository->findAllJoinRoles();
        $this->template->setFile(__DIR__.'/templates/summaryModal.latte');
        $this->template->render();
    }
}