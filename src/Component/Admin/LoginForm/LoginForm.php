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
use Wakers\LangModule\Translator\Translate;
use Wakers\UserModule\Security\Authenticator;


class LoginForm extends BaseControl
{
    use AjaxValidate;


    /**
     * @var Authenticator
     */
    protected $authenticator;


    /**
     * @var Translate
     */
    protected $translate;


    /**
     * LoginForm constructor.
     * @param Authenticator $authenticator
     * @param Translate $translate
     */
    public function __construct(Authenticator $authenticator, Translate $translate)
    {
        $this->authenticator = $authenticator;
        $this->translate = $translate;
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
            ->setRequired('E-mail is required.')
            ->addRule(Form::EMAIL, 'E-mail is not valid.');

        $form->addPassword('password')
            ->setRequired('Password is required.');

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
                    $this->translate->translate('Logged in'),
                    $this->translate->translate('You have successfully logged in.'),
                    'success'
                );

                $this->presenter->redirect(':Base:Admin:Dashboard');
            }

            catch (AuthenticationException $exception)
            {
                $this->presenter->notificationAjax(
                    $this->translate->translate('Error'),
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