<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\UserModule\Component\Frontend\PasswordForm;


use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Form;
use Wakers\BaseModule\Component\Frontend\BaseControl;
use Wakers\BaseModule\Util\AjaxValidate;
use Wakers\UserModule\Database\User;
use Wakers\UserModule\Manager\UserManager;
use Wakers\UserModule\Security\UserAuthorizator;


class PasswordForm extends BaseControl
{
    use AjaxValidate;


    /**
     * @var UserManager
     */
    protected $userManager;


    /**
     * @var User
     */
    protected $userEntity;


    /**
     * PasswordForm constructor.
     * @param User $userEntity
     * @param UserManager $userManager
     */
    public function __construct(User $userEntity, UserManager $userManager)
    {
        $this->userEntity = $userEntity;
        $this->userManager = $userManager;
    }


    /**
     * Render
     */
    public function render() : void
    {
        $this->template->userEntity = $this->userEntity;
        $this->template->setFile(__DIR__ . '/templates/passwordForm.latte');
        $this->template->render();
    }


    /**
     * @return Form
     */
    protected function createComponentPasswordForm() : Form
    {
        $disabled = $this->userEntity->getId() !== $this->presenter->user->getId();

        $form = new Form;

        $form->addPassword('password')
            ->setRequired('Heslo je povinné.')
            ->addRule(Form::PATTERN, 'Povolené znaky hesla: A-Z, a-z, 0-9.', '((?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{1,}).*')
            ->addRule(Form::MIN_LENGTH, 'Mín. délka hesla je %s znaků', 10)
            ->setDisabled($disabled);

        $form->addPassword('passwordCheck')
            ->setRequired('Ověření hesla je povinné.')
            ->addRule(Form::EQUAL, 'Hesla se neshodují.', $form['password'])
            ->setDisabled($disabled);

        $form->addSubmit('save')
            ->setDisabled($disabled);


        $form->onValidate[] = function (Form $form) { $this->validate($form); };
        $form->onSuccess[] = function (Form $form) { $this->success($form); };

        return $form;
    }


    /**
     * @param Form $form
     * @throws ForbiddenRequestException
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

            $this->userManager->savePassword($this->userEntity, $values->password);

            $this->presenter->notificationAjax(
                'Heslo upraveno',
                'Heslo bylo úspěšně upraveno.',
                'success',
                FALSE
            );

            $form->reset();
            $this->getParent()->onSavePassword();
        }
    }


    /**
     * Handler pro reset hesla
     * @throws \Exception
     */
    public function handleResetPassword()
    {
        if ($this->presenter->isAjax())
        {
            $allowedReset = (
                $this->presenter->user->isAllowed(UserAuthorizator::RES_RESET_PASSWORD_HANDLE)
                && $this->presenter->user->getId() !== $this->userEntity->getId()
                && (
                    !$this->userEntity->isInRole(UserAuthorizator::ROLE_ADMIN)
                    || $this->presenter->user->isAllowed(UserAuthorizator::RES_EDIT_ADMINS)
                )
            );

            if (!$allowedReset)
            {
                throw new ForbiddenRequestException;
            }

            try
            {
                $this->userManager->resetPassword($this->userEntity);

                $this->presenter->notificationAjax(
                    'Heslo resetováno',
                    'Heslo bylo úspěšně resetováno a uživateli byl odeslán e-mail.',
                    'success'
                );
            }
            catch (\Exception $exception)
            {
                throw $exception;
            }
        }
    }
}