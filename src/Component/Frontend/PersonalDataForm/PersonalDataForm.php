<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\UserModule\Component\Frontend\PersonalDataForm;


use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Form;
use Wakers\BaseModule\Component\Frontend\BaseControl;
use Wakers\BaseModule\Util\AjaxValidate;
use Wakers\LangModule\Translator\Translate;
use Wakers\UserModule\Database\User;
use Wakers\UserModule\Manager\UserManager;
use Wakers\UserModule\Manager\UserPersonalDataManager;
use Wakers\UserModule\Security\UserAuthorizator;


class PersonalDataForm extends BaseControl
{
    use AjaxValidate;


    /**
     * @var UserManager
     */
    protected $userManager;


    /**
     * @var UserPersonalDataManager
     */
    protected $userPersonalDataManager;


    /**
     * @var User
     */
    protected $userEntity;


    /**
     * @var Translate
     */
    protected $translate;


    /**
     * PrimaryForm constructor.
     * @param User $userEntity
     * @param UserManager $userManager
     * @param UserPersonalDataManager $userPersonalDataManager
     * @param Translate $translate
     */
    public function __construct(
        User $userEntity,
        UserManager $userManager,
        UserPersonalDataManager $userPersonalDataManager,
        Translate $translate
    ) {
        $this->userEntity = $userEntity;
        $this->userManager = $userManager;
        $this->userPersonalDataManager = $userPersonalDataManager;
        $this->translate = $translate;
    }


    /**
     * Render
     */
    public function render() : void
    {
        $this->template->userEntity = $this->userEntity;
        $this->template->setFile(__DIR__ . '/templates/personalDataForm.latte');
        $this->template->render();
    }


    /**
     * @return Form
     * @throws \Propel\Runtime\Exception\PropelException
     */
    protected function createComponentPrimaryForm() : Form
    {
        // Oprávnění formuláře
        $disabled = (
            (
                $this->userEntity->getId() !== $this->presenter->user->getId()
                && !$this->presenter->user->isAllowed(UserAuthorizator::RES_EDIT_PERSONAL_DATA_FORM)
            )
            ||
            (
                $this->userEntity->isInRole(UserAuthorizator::ROLE_ADMIN)
                && !$this->presenter->user->isAllowed(UserAuthorizator::RES_EDIT_ADMINS)
            )
        );


        $form = new Form;

        $form->addText('disabledId')
            ->setDisabled();

        $form->addEmail('disabledEmail')
            ->setDisabled();

        $form->addText('phone')
            ->setRequired(FALSE)
            ->setDisabled($disabled);

        $form->addText('firstName')
            ->setRequired(FALSE)
            ->setDisabled($disabled);

        $form->addText('lastName')
            ->setRequired(FALSE)
            ->setDisabled($disabled);

        $form->addSubmit('save')
            ->setDisabled($disabled);


        $form->onValidate[] = function (Form $form) { $this->validate($form); };
        $form->onSuccess[] = function (Form $form) { $this->success($form); };


        $defaults = [
            'disabledId' => $this->userEntity->getId(),
            'disabledEmail' => $this->userEntity->getEmail(),
        ];

        if ($this->userEntity->getUserPersonalData())
        {
            $defaults += [
                'phone' => $this->userEntity->getUserPersonalData()->getPhone(),
                'firstName' => $this->userEntity->getUserPersonalData()->getFirstName(),
                'lastName' => $this->userEntity->getUserPersonalData()->getLastName()
            ];
        }

        $form->setDefaults($defaults);

        return $form;
    }


    /**
     * Success Primary
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

            $this->userManager->getConnection()->beginTransaction();

            try
            {
                $this->userPersonalDataManager->saveName($this->userEntity, $values->firstName, $values->lastName);
                $this->userPersonalDataManager->savePhone($this->userEntity, $values->phone);

                $this->userManager->getConnection()->commit();

                $this->presenter->notificationAjax(
                    $this->translate->translate('User edited'),
                    $this->translate->translate("User %email% successfully edited", ['email' => $this->userEntity->getEmail()]),
                    'success'
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