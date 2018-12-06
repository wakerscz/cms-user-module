<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\UserModule\Manager;


use Latte\Engine;
use Nette\Application\LinkGenerator;
use Nette\DateTime;
use Nette\Mail\Message;
use Nette\Mail\SmtpMailer;
use Nette\Utils\Random;
use Propel\Runtime\Exception\PropelException;
use Wakers\BaseModule\Database\AbstractDatabase;
use Wakers\BaseModule\Database\DatabaseException;
use Wakers\LangModule\Translator\Translate;
use Wakers\UserModule\Database\User;
use Wakers\UserModule\Repository\UserRepository;
use Wakers\UserModule\Security\UserAuthorizator;


class UserManager extends AbstractDatabase
{
    /**
     * Adresář se šabolonami mailů
     */
    const MAIL_TEMPLATE_PATH = __DIR__ . '/../../../../../app/template/mail';


    /**
     * Pravidla pro vygenerování náhodného hesla
     */
    const
        PASSWORD_GENERATOR_STRING = '0-9A-Za-z\_\@\-',
        PASSWORD_GENERATOR_LENGTH = 12;


    /**
     * @var array
     */
    protected $smtp;


    /**
     * @var UserRepository
     */
    protected $userRepository;


    /**
     * @var LinkGenerator
     */
    protected $linkGenerator;


    /**
     * @var Translate
     */
    protected $translate;


    /**
     * UserManager constructor.
     * @param array $smtp
     * @param UserRepository $userRepository
     * @param LinkGenerator $linkGenerator
     */
    public function __construct(array $smtp, UserRepository $userRepository, LinkGenerator $linkGenerator, Translate $translate)
    {
        $this->smtp = $smtp;
        $this->userRepository = $userRepository;
        $this->linkGenerator = $linkGenerator;
        $this->translate = $translate;
    }


    /**
     * @param string $email
     * @param string|null $password
     * @param string $status
     * @return User
     * @throws DatabaseException
     * @throws \Exception
     */
    public function add(string $email, string $password = NULL, string $status = UserAuthorizator::STATUS_PENDING_APPROVAL) : User
    {
        $user = $this->userRepository->findOneByEmail($email);

        if ($user !== NULL)
        {
            $message = $this->translate->translate("User with e-mail '%email%' is already registered.", ['email' => $email]);
            throw new DatabaseException($email);
        }

        $user = new User;
        $user->setEmail($email);
        $user->setStatus($status);
        $user->setPassword($password);
        $user->save();

        if ($password === NULL)
        {
            $this->savePasswordAndSendMail($user, 'createUser');
        }

        return $user;
    }


    /**
     * @param User $user
     * @param DateTime $dateTime
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function saveLastLogin(User $user, DateTime $dateTime) : void
    {
        $user->setLastLogin($dateTime);
        $user->save();
    }


    /**
     * @param User $user
     * @param string $password
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function savePassword(User $user, string $password)
    {
        $user->setPassword($password);
        $user->save();
    }


    /**
     * @param User $user
     * @param string $status
     * @throws PropelException
     */
    public function saveStatus(User $user, string $status)
    {
        $user->setStatus($status);
        $user->save();
    }


    /**
     * @param User $user
     * @throws PropelException
     */
    public function delete(User $user)
    {
        $user->delete();
    }


    /**
     * @param User $user
     * @throws \Exception
     */
    public function resetPassword(User $user) : void
    {
        $this->savePasswordAndSendMail($user, 'resetPassword');
    }


    /**
     * @param User $user
     * @param string $configName
     * @throws \Exception
     */
    protected function savePasswordAndSendMail(User $user, string $configName) : void
    {
        $pr = $this->smtp[$configName];

        $password = Random::generate(self::PASSWORD_GENERATOR_LENGTH, self::PASSWORD_GENERATOR_STRING);

        $template = new Engine;

        $html = $template->renderToString(self::MAIL_TEMPLATE_PATH . "/{$configName}.latte", [
            'user' => $user,
            'sender' => $pr['sender'],
            'url' => $this->linkGenerator->link('App:Run:setUrl'),
            'password' => $password,
            'translate' => $this->translate
        ]);

        $message = (new Message)
            ->setFrom($pr['sender']['email'], $pr['sender']['name'])
            ->addTo($user->getEmail())
            ->setHtmlBody($html);

        $smtp = new SmtpMailer($pr['config']);

        $this->getConnection()->beginTransaction();

        try
        {
            $user->setPassword($password);
            $user->save();

            $smtp->send($message);
            $this->getConnection()->commit();
        }
        catch (\Exception $exception)
        {
            $this->getConnection()->rollBack();
            throw $exception;
        }
    }
}