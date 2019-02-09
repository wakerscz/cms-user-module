<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\UserModule\Component\Admin\LoginForm;


use Nette\Application\UI\Form;
use Nette\Security\AuthenticationException;
use Propel\Runtime\Exception\PropelException;
use Wakers\BaseModule\Component\Admin\BaseControl;
use Wakers\BaseModule\Util\AjaxValidate;
use Wakers\UserModule\Security\Authenticator;


class LoginForm extends BaseControl
{
    use AjaxValidate;


    /**
     * @var Authenticator
     */
    protected $authenticator;



    /**
     * LoginForm constructor.
     * @param Authenticator $authenticator
     */
    public function __construct(Authenticator $authenticator)
    {
        $this->authenticator = $authenticator;
    }


    /**
     * Render
     */
    public function render() : void
    {
        $this->template->setFile(__DIR__ . '/templates/loginForm.latte');
        $this->template->render();
    }


    /**
     * Login Form
     * @return Form
     */
    public function createComponentLoginForm() : Form
    {
        $form = new Form;

        $form->addText('email')
            ->setRequired('E-mail je povinný.')
            ->addRule(Form::EMAIL, 'E-mail není ve správném formátu.');

        $form->addPassword('password')
            ->setRequired('Heslo je povinné.');

        $form->addSubmit('login');

        $form->onValidate[] = function (Form $form) { $this->validate($form); };
        $form->onSuccess[] = function (Form $form) { $this->success($form); };

        return $form;
    }


    /**
     * @param Form $form
     * @throws PropelException
     * @throws \Nette\Application\AbortException
     */
    public function success(Form $form)
    {
        if ($this->presenter->isAjax() && !$this->presenter->user->isLoggedIn())
        {
            $values = $form->getValues();

            try
            {
                $identity = $this->authenticator->authenticate([$values->email, $values->password]);

                $this->presenter->user->login($identity);

                $this->presenter->notification(
                    'Přihlášení',
                    'Byli jste úspěšně přihlášení.',
                    'success'
                );

                $this->presenter->redirect(':Base:Admin:Dashboard');
            }

            catch (AuthenticationException $exception)
            {
                $this->presenter->notificationAjax(
                    'Chyba',
                    $exception->getMessage(),
                    'error'
                );
            }

            catch (PropelException $exception)
            {
                throw $exception;
            }
        }
    }
}