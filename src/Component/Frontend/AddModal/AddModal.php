<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\UserModule\Component\Frontend\AddModal;


use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Form;
use Wakers\BaseModule\Component\Frontend\BaseControl;
use Wakers\BaseModule\Util\AjaxValidate;
use Wakers\BaseModule\Database\DatabaseException;
use Wakers\LangModule\Translator\Translate;
use Wakers\UserModule\Manager\UserManager;
use Wakers\UserModule\Manager\UserPersonalDataManager;
use Wakers\UserModule\Manager\UserRoleManager;
use Wakers\UserModule\Security\UserAuthorizator;


class AddModal extends BaseControl
{
    use AjaxValidate;


    /**
     * @var UserManager
     */
    protected $userManager;


    /**
     * @var UserRoleManager
     */
    protected $userRoleManager;


    /**
     * @var UserPersonalDataManager
     */
    protected $userPersonalDataManager;


    /**
     * @var Translate
     */
    protected $translate;


    /**
     * Callback volaný po vytvoření uživatele
     * @var callable
     */
    public $onSave = [];


    /**
     * AddModal constructor.
     * @param UserManager $userManager
     * @param UserRoleManager $userRoleManager
     * @param UserPersonalDataManager $userPersonalDataManager
     * @param Translate $translate
     */
    public function __construct(
        UserManager $userManager,
        UserRoleManager $userRoleManager,
        UserPersonalDataManager $userPersonalDataManager,
        Translate $translate
    ) {
        $this->userManager = $userManager;
        $this->userRoleManager = $userRoleManager;
        $this->userPersonalDataManager = $userPersonalDataManager;
        $this->translate = $translate;
    }


    /**
     * Render
     */
    public function render() : void
    {
        $this->template->setFile(__DIR__ . '/templates/addModal.latte');
        $this->template->render();
    }


    /**
     * Formulář pro přidání uživatele
     * @return Form
     */
    protected function createComponentAddForm() : Form
    {
        $roles = UserAuthorizator::ALL_ROLE_KEYS;

        $disabled = !$this->presenter->user->isAllowed(UserAuthorizator::RES_ADD_FORM);

        $form = new Form;

        $form->addText('email')
            ->addRule(Form::EMAIL, 'Invalid e-mail format.')
            ->setRequired('E-mail is required.')
            ->setDisabled($disabled);

        $form->addText('password')
            ->setDisabled();

        $form->addSelect('role', NULL, $roles)
            ->setRequired('User role is required.')
            ->setDisabled($disabled);

        $form->addSelect('status', NULL, UserAuthorizator::ALL_STATUS_KEYS)
            ->setRequired('User status is required.')
            ->setDisabled($disabled);

        $form->addText('phone')
            ->setRequired(FALSE)
            ->setDisabled($disabled);

        $form->addText('firstName')
            ->setRequired(FALSE)
            ->setDisabled($disabled);

        $form->addText('lastName')
            ->setRequired(FALSE)
            ->setDisabled($disabled);

        $form->addSubmit('save');


        // Pouze admin může někomu přidat admin práva
        if(!$disabled && !$this->presenter->user->isAllowed(UserAuthorizator::RES_EDIT_ADMINS))
        {
            $key = array_search(UserAuthorizator::ROLE_ADMIN, $roles);
            $form['role']->setDisabled([$key]);
        }


        $form->onValidate[] = function (Form $form) { $this->validate($form); };
        $form->onSuccess[] = function (Form $form) { $this->success($form); };

        return $form;
    }


    /**
     * Success Form
     * @param Form $form
     * @throws \Exception
     */
    public function success(Form $form) : void
    {
        if ($this->presenter->isAjax())
        {
            $values = $form->getValues();

            if ($values->count() === 0)
            {
                throw new ForbiddenRequestException;
            }

            $status = UserAuthorizator::ALL_STATUS_KEYS[$values->status];
            $role = UserAuthorizator::ALL_ROLE_KEYS[$values->role];

            $this->userManager->getConnection()->beginTransaction();

            try
            {
                $user = $this->userManager->add($values->email, NULL, $status);
                $this->userRoleManager->addRole($user, $role);
                
                $this->userPersonalDataManager->saveName($user, $values->firstName, $values->lastName);
                $this->userPersonalDataManager->savePhone($user, $values->phone);

                $this->userManager->getConnection()->commit();

                $this->presenter->notificationAjax(
                    $this->translate->translate('User created'),
                    $this->translate->translate('User %email% successfully created.', ['email' => $values->email]),
                    'success',
                    FALSE
                );

                $this->presenter->handleModalToggle('hide', '#wakers_user_add_modal', FALSE);

                $form->reset();
                $this->onSave();

            }
            catch (DatabaseException $exception)
            {
                $this->userManager->getConnection()->rollBack();

                $this->presenter->notificationAjax(
                    $this->translate->translate('Error'),
                    $exception->getMessage(),
                    'error'
                );
            }
            catch (\Exception $exception)
            {
                $this->userManager->getConnection()->rollBack();
                throw $exception;
            }
        }
    }
}