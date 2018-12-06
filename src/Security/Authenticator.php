<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\UserModule\Security;


use Nette\DateTime;
use Nette\Security\AuthenticationException;
use Nette\Security\IAuthenticator;
use Nette\Security\Identity;
use Wakers\LangModule\Translator\Translate;
use Wakers\UserModule\Manager\UserManager;
use Wakers\UserModule\Repository\UserRepository;
use Wakers\UserModule\Repository\UserRoleRepository;


class Authenticator implements IAuthenticator
{
    /**
     * @var UserRepository
     */
    protected $userRepository;


    /**
     * @var UserRoleRepository
     */
    protected $userRoleRepository;


    /**
     * @var UserManager
     */
    protected $userManager;


    /**
     * @var Translate
     */
    protected $translate;


    public function __construct(
        UserRepository $userRepository,
        UserRoleRepository $userRoleRepository,
        UserManager $userManager,
        Translate $translate
    ) {
        $this->userRepository = $userRepository;
        $this->userRoleRepository = $userRoleRepository;
        $this->userManager = $userManager;
        $this->translate = $translate;
    }


    /**
     * @param array $credentials
     * @return Identity|\Nette\Security\IIdentity
     * @throws AuthenticationException
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function authenticate(array $credentials)
    {
        list ($email, $password) = $credentials;

        $user = $this->userRepository->findOneByEmailJoinRoles($email);

        if ($user === NULL)
        {
            $message = $this->translate->translate('User %email% does not exists', ['email' => $email]);
            throw new AuthenticationException($message);
        }

        if (Passwords::verify($password, $user->getPassword()) === FALSE)
        {
            $message = $this->translate->translate('Incorrect password.');
            throw new AuthenticationException($message);
        }

        $statusKey = array_search(UserAuthorizator::STATUS_APPROVED, UserAuthorizator::ALL_STATUS_KEYS);

        if ($user->getStatus() !== $statusKey)
        {
            $status = UserAuthorizator::ALL_STATUS_KEYS[$user->getStatus()];
            $message = $this->translate->translate('Cannot log-in because your account has not yet been approved. Your actual account status: %status%', ['status' => $status]);

            throw new AuthenticationException($message);
        }

        $roles = $this->userRoleRepository->findByUser_asArray($user);

        $this->userManager->saveLastLogin($user, new DateTime);

        return new Identity($user->getId(), $roles, [
            'userEntity' => $user,
            'userPersonalData' => $user->getUserPersonalData(),
            'roles' => $user->getUserRoles()
        ]);
    }
}