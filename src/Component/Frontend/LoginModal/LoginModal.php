<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\UserModule\Component\Frontend\LoginModal;


use Nette\Application\UI\Form;
use Nette\Security\AuthenticationException;
use Wakers\UserModule\Component\Admin\LoginForm\LoginForm;


class LoginModal extends LoginForm
{
    /**
     * Render
     */
    public function render() : void
    {
        $this->template->setFile(__DIR__ . '/templates/loginModal.latte');
        $this->template->render();
    }


    /**
     * Success form
     *
     * @param Form $form
     * @throws \Exception
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

                $this->presenter->redirect('this');
            }

            catch (AuthenticationException $exception)
            {
                $this->presenter->notificationAjax(
                    $this->translate->translate('Error'),
                    $exception->getMessage(),
                    'error'
                );
            }

            catch (\Exception $exception)
            {
                throw $exception;
            }
        }
    }
}