<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\UserModule\Component\Frontend\PermissionForm;


use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Form;
use Wakers\BaseModule\Component\Frontend\BaseControl;
use Wakers\BaseModule\Util\AjaxValidate;
use Wakers\LangModule\Translator\Translate;
use Wakers\UserModule\Database\User;
use Wakers\UserModule\Manager\UserManager;
use Wakers\UserModule\Manager\UserRoleManager;
use Wakers\UserModule\Security\UserAuthorizator;


class PermissionForm extends BaseControl
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
     * @var User
     */
    protected $userEntity;


    /**
     * @var Translate
     */
    protected $translate;


    /**
     * PermissionForm constructor.
     * @param User $userEntity
     * @param UserManager $userManager
     * @param UserRoleManager $userRoleManager
     * @param Translate $translate
     */
    public function __construct(
        User $userEntity,
        UserManager $userManager,
        UserRoleManager $userRoleManager,
        Translate $translate
    ) {
        $this->userEntity = $userEntity;
        $this->userManager = $userManager;
        $this->userRoleManager = $userRoleManager;
        $this->translate = $translate;
    }


    /**
     * Render
     */
    public function render() : void
    {
        $this->template->userEntity = $this->userEntity;
        $this->template->setFile(__DIR__ . '/templates/permissionForm.latte');
        $this->template->render();
    }


    /**
     * @return Form
     * @throws \Propel\Runtime\Exception\PropelException
     */
    protected function createComponentPermissionForm() : Form
    {
        $roles = UserAuthorizator::ALL_ROLE_KEYS;

        // Oprávnění formuláře
        $disabled = (
            (
                $this->userEntity->isInRole(UserAuthorizator::ROLE_ADMIN)
                && !$this->presenter->user->isAllowed(UserAuthorizator::RES_EDIT_ADMINS)
            )
            || !$this->presenter->user->isAllowed(UserAuthorizator::RES_EDIT_PERMISSION_FORM)
        );


        $form = new Form;

        $form->addSelect('role', NULL, $roles)
            ->setRequired('Role is required.')
            ->setDisabled($disabled);

        $form->addSelect('status', NULL, UserAuthorizator::ALL_STATUS_KEYS)
            ->setRequired('Status is required')
            ->setDisabled($disabled);

        $form->addSubmit('save')
            ->setDisabled($disabled);

        $form->setDefaults([
            'status' => $this->userEntity->getStatus(),
            'role' => $this->userEntity->getUserRoles()->get(0)->getRoleKey() // Každý uživatel může mít pouze jednu roli relace 1:1
        ]);

        // Pouze admin může měnit práva admina
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
     * @param Form $form
     * @throws \Exception
     */
    protected function success(Form $form) : void
    {
        if ($this->presenter->isAjax())
        {
            $values = $form->getValues();

            if ($values->count() === 0)
            {
                throw new ForbiddenRequestException;
            }

            $status = UserAuthorizator::ALL_STATUS_KEYS[$values->status];

            $this->userManager->getConnection()->beginTransaction();

            try
            {
                $this->userRoleManager->updateRole($this->userEntity, $values->role);
                $this->userManager->saveStatus($this->userEntity, $status);
                $this->userManager->getConnection()->commit();

                $this->presenter->notificationAjax(
                    $this->translate->translate('Permissions updated'),
                    $this->translate->translate('User permissions successfully updated.'),
                    'success',
                    FALSE
                );

                $this->getParent()->onSave();
            }

            catch (\Exception $exception)
            {
                $this->userManager->getConnection()->rollBack();
                throw $exception;
            }
        }
    }
}