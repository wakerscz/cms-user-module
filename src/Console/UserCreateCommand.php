<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\UserModule\Console;


use Propel\Runtime\Propel;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Wakers\UserModule\Database\Map\UserTableMap;
use Wakers\UserModule\Manager\UserManager;
use Wakers\UserModule\Manager\UserRoleManager;
use Wakers\UserModule\Security\UserAuthorizator;


class UserCreateCommand extends Command
{
    /**
     * Configuration
     */
    protected function configure() : void
    {
        $this
            ->setName('wakers:admin-create')
            ->setDescription('Creating new admin user')
            ->addArgument('email', InputArgument::REQUIRED, 'E-mail address')
            ->addArgument('password', InputArgument::REQUIRED, 'Password')
        ;
    }


    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Propel\Runtime\Exception\PropelException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /**
         * @var UserManager $userManager
         * @var UserRoleManager $userRoleManager
         */

        $userManager = $this->getHelper('container')->getByType(UserManager::class);
        $userRoleManager = $this->getHelper('container')->getByType(UserRoleManager::class);

        $email = $input->getArgument('email');
        $password = $input->getArgument('password');


        $con = Propel::getWriteConnection(UserTableMap::DATABASE_NAME);
        $con->beginTransaction();

        try
        {
            $user = $userManager->add($email, $password, UserAuthorizator::STATUS_APPROVED);
            $userRoleManager->addRole($user, UserAuthorizator::ROLE_ADMIN);

            $con->commit();

            $output->writeln("<info>User {$email} successfully created.</info>");
        }

        catch (\Exception $exception)
        {
            $con->rollBack();
            $output->writeln("<error>{$exception->getMessage()}</error>");
        }

    }
}