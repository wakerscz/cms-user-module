<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\UserModule\Component\Frontend\EditModal;


use Nette\Application\ForbiddenRequestException;
use Wakers\BaseModule\Component\Frontend\BaseControl;
use Wakers\UserModule\Component\Frontend\PasswordForm\IPasswordForm;
use Wakers\UserModule\Component\Frontend\PasswordForm\PasswordForm;
use Wakers\UserModule\Component\Frontend\PermissionForm\IPermissionForm;
use Wakers\UserModule\Component\Frontend\PermissionForm\PermissionForm;
use Wakers\UserModule\Component\Frontend\PersonalDataForm\IPersonalDataForm;
use Wakers\UserModule\Component\Frontend\PersonalDataForm\PersonalDataForm;
use Wakers\UserModule\Database\User;
use Wakers\UserModule\Repository\UserRepository;
use Wakers\UserModule\Security\UserAuthorizator;


class EditModal extends BaseControl
{
    /**
     * @var IPasswordForm;
     */
    protected $IPasswordForm;


    /**
     * @var IPersonalDataForm
     */
    protected $IPersonalDataForm;


    /**
     * @var IPermissionForm
     */
    protected $IPermissionForm;


    /**
     * @var UserRepository
     */
    protected $userRepository;


    /**
     * Entita načtena při otevření modálního okna
     * @var User
     */
    protected $userEntity;


    /**
     * Callback volaný po uložení (změn) uživatele
     * @var callable
     */
    public $onSave = [];


    /**
     * @var callable
     */
    public $onOpen = [];


    /**
     * @var callable
     */
    public $onSavePassword = [];


    /**
     * @var int
     * @persistent
     */
    public $userId;


    /**
     * EditModal constructor.
     * @param UserRepository $userRepository
     * @param IPasswordForm $IPasswordForm
     * @param IPersonalDataForm $IPersonalDataForm
     * @param IPermissionForm $IPermissionForm
     */
    public function __construct(UserRepository $userRepository, IPasswordForm $IPasswordForm, IPersonalDataForm $IPersonalDataForm, IPermissionForm $IPermissionForm)
    {
        $this->userRepository = $userRepository;
        $this->IPasswordForm = $IPasswordForm;
        $this->IPersonalDataForm = $IPersonalDataForm;
        $this->IPermissionForm = $IPermissionForm;
    }


    /**
     * Render
     */
    public function render() : void
    {
        $this->template->userEntity = $this->getUserEntity();;
        $this->template->setFile(__DIR__ . '/templates/editModal.latte');
        $this->template->render();
    }


    /**
     * Handler pro otevření modálního okna, načte userId do persistentní proměnné
     * @param int $userId
     * @throws \Exception
     */
    public function handleOpen(int $userId) : void
    {
        if ($this->presenter->isAjax())
        {
            if (!$this->presenter->user->isAllowed(UserAuthorizator::RES_EDIT_MODAL))
            {
                throw new ForbiddenRequestException;
            }

            $this->userId = $userId;

            $this->presenter->handleModalToggle('show', '#wakers_user_edit_modal', FALSE);

            $this->onOpen();
        }
    }


    /**
     * @return PermissionForm
     */
    protected function createComponentPermissionForm() : PermissionForm
    {
        $ue = $this->getUserEntity();
        $control = $this->IPermissionForm->create($ue);

        return $control;
    }


    /**
     * @return PersonalDataForm
     */
    protected function createComponentPersonalDataForm() : PersonalDataForm
    {
        $ue = $this->getUserEntity();
        $control = $this->IPersonalDataForm->create($ue);

        return $control;
    }


    /**
     * @return PasswordForm
     */
    protected function createComponentPasswordForm() : PasswordForm
    {
        $ue = $this->getUserEntity();
        $control = $this->IPasswordForm->create($ue);

        return $control;
    }


    /**
     * Metoda optimalizuje počet DB queries.
     * Načte data pokud existuje persistentní $userId a $userEntity nebyla dosud načtena
     * @return null|User
     */
    protected function getUserEntity() : ?User
    {
        if (!$this->userEntity && $this->userId)
        {
            $this->userEntity = $this->userRepository->findOneById($this->userId);
        }

        return $this->userEntity;
    }
}