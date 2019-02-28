<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\UserModule\Security;


use Nette\Utils\DateTime;
use Nette\Security\AuthenticationException;
use Nette\Security\IAuthenticator;
use Nette\Security\Identity;
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
     * Authenticator constructor.
     * @param UserRepository $userRepository
     * @param UserRoleRepository $userRoleRepository
     * @param UserManager $userManager
     */
    public function __construct(
        UserRepository $userRepository,
        UserRoleRepository $userRoleRepository,
        UserManager $userManager
    ) {
        $this->userRepository = $userRepository;
        $this->userRoleRepository = $userRoleRepository;
        $this->userManager = $userManager;
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
            throw new AuthenticationException("Uživatel '{$email}' neexistuje.");
        }

        if (Passwords::verify($password, $user->getPassword()) === FALSE)
        {
            throw new AuthenticationException("Zadali jste nesprávné heslo.");
        }

        $statusKey = array_search(UserAuthorizator::STATUS_APPROVED, UserAuthorizator::ALL_STATUS_KEYS);

        if ($user->getStatus() !== $statusKey)
        {
            $status = UserAuthorizator::ALL_STATUS_KEYS[$user->getStatus()];

            throw new AuthenticationException("Nelze se přihlásit - účet ještě nebyl schválen. Váš aktuální status je: '{$status}'.");
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