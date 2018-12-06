<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\UserModule\Component\Frontend\RemoveModal;


use Nette\Application\ForbiddenRequestException;
use Wakers\BaseModule\Component\Frontend\BaseControl;
use Wakers\LangModule\Translator\Translate;
use Wakers\UserModule\Database\User;
use Wakers\UserModule\Manager\UserManager;
use Wakers\UserModule\Repository\UserRepository;
use Wakers\UserModule\Security\UserAuthorizator;


class RemoveModal extends BaseControl
{
    /**
     * @var UserRepository
     */
    protected $userRepository;


    /**
     * @var UserManager
     */
    protected $userManager;


    /**
     * Entita načtena při otevření modálního okna
     * @var User
     */
    protected $userEntity;


    /**
     * @var Translate
     */
    protected $translate;


    /**
     * Callback volaný po odstranění uživatele
     * @var callable
     */
    public $onRemove = [];

    /**
     * @var callable
     */
    public $onOpen = [];


    /**
     * RemoveModal constructor.
     * @param UserRepository $userRepository
     * @param UserManager $userManager
     * @param Translate $translate
     */
    public function __construct(UserRepository $userRepository, UserManager $userManager, Translate $translate)
    {
        $this->userRepository = $userRepository;
        $this->userManager = $userManager;
        $this->translate = $translate;
    }


    /**
     * Render
     */
    public function render() : void
    {
        $this->template->userEntity = $this->userEntity;
        $this->template->setFile(__DIR__ . '/templates/removeModal.latte');
        $this->template->render();
    }


    /**
     * Handler pro otevření modálního okna a userEntity
     * @param int $userId
     * @throws ForbiddenRequestException
     */
    public function handleOpen(int $userId) : void
    {
        if ($this->presenter->isAjax())
        {
            if (!$this->presenter->user->isAllowed(UserAuthorizator::RES_REMOVE_HANDLE))
            {
                throw new ForbiddenRequestException;
            }

            $this->userEntity = $this->userRepository->findOneById($userId);

            $this->presenter->handleModalToggle('show', '#wakers_user_remove_modal', FALSE);
            $this->onOpen();
        }
    }


    /**
     * Handler pro odstranění uživatele
     * @param int $userId
     * @throws \Exception
     */
    public function handleRemove(int $userId) : void
    {
        if ($this->presenter->isAjax())
        {
            $this->userEntity = $this->userRepository->findOneById($userId);

            $allowRemove = (
                (
                    !$this->userEntity->isInRole(UserAuthorizator::ROLE_ADMIN)
                    || $this->presenter->user->isAllowed(UserAuthorizator::RES_EDIT_ADMINS)
                )
                && $this->presenter->user->isAllowed(UserAuthorizator::RES_REMOVE_HANDLE)
                && $userId !== $this->presenter->user->getId()
            );

            if (!$allowRemove)
            {
                throw new ForbiddenRequestException;
            }

            $this->userManager->delete($this->userEntity);

            $this->presenter->notificationAjax(
                $this->translate->translate('User removed'),
                $this->translate->translate("User %email% successfully removed.", ['email' => $this->userEntity->getEmail()]),
                    'success',
                    FALSE
                );

            $this->presenter->handleModalToggle('hide', '#wakers_user_remove_modal', FALSE);

            $this->onRemove();
        }
    }
}