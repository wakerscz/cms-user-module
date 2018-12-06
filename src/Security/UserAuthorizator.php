<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\UserModule\Security;


use Wakers\BaseModule\Builder\AclBuilder\AuthorizatorBuilder;


class UserAuthorizator extends AuthorizatorBuilder
{
    const
        ROLE_ADMIN = "admin",                     // Admin
        ROLE_EDITOR = "editor",                   // Editor
        ROLE_AUTHENTICATED = "authenticated",     // Registrovaný (bez role)
        ROLE_GUEST = 'guest'                      // Návštěvník
    ;


    const
        STATUS_PENDING_APPROVAL = 'STATUS_PENDING_APPROVAL',    // Uživatel čeká na schálení
        STATUS_NOT_APPROVED = 'STATUS_NOT_APPROVED',            // Uživatel nebyl schválen
        STATUS_APPROVED = 'STATUS_APPROVED'                     // Uživatel je schválen
    ;


    const ALL_ROLE_KEYS = [
        3000 => self::ROLE_AUTHENTICATED,
        4000 => self::ROLE_EDITOR,
        5000 => self::ROLE_ADMIN,
    ];


    const ALL_STATUS_KEYS = [
        0 => self::STATUS_PENDING_APPROVAL,
        1 => self::STATUS_NOT_APPROVED,
        2 => self::STATUS_APPROVED
    ];


    const
        RES_USER_MODULE = 'USER_RES_MODULE',                                // Modul (zobrazit na dashboardu)
        RES_SUMMARY = 'USER_RES_SUMMARY',                                   // Přehled uživatelů
        RES_EDIT_MODAL = 'USER_RES_EDIT_MODAL',                             // Modální okno pro editaci uživatelů
        RES_EDIT_ADMINS = 'USER_RES_EDIT_ADMINS',                           // Pracovat s adminy (př editor nemůže upravit admina)
        RES_ADD_FORM = 'USER_RES_ADD_FORM',                                 // Přidat uživatele
        RES_EDIT_PERSONAL_DATA_FORM = 'USER_RES_EDIT_PERSONAL_DATA_FORM',   // Editovat hl. nastavení (ostatním uživatelů)
        RES_EDIT_PERMISSION_FORM = 'USER_RES_EDIT_PERMISSION_FORM',         // Editovat práva (ostatním uživatelům)
        RES_RESET_PASSWORD_HANDLE = 'USER_RES_RESET_PASSWORD_HANDLE',       // Vygenerovat uživ. nové heslo
        RES_REMOVE_HANDLE = 'USER_RES_REMOVE_HANDLE'                        // Odstranit uživatele
    ;

    /**
     * @return array
     */
    public function create() : array
    {
        /*
         * Roles
         */

        $this->addRole(self::ROLE_GUEST);
        $this->addRole(self::ROLE_AUTHENTICATED);
        $this->addRole(self::ROLE_EDITOR);
        $this->addRole(self::ROLE_ADMIN, [self::ROLE_EDITOR]);


        /*
         * Resources
         */

        $this->addResource(self::RES_USER_MODULE);
        $this->addResource(self::RES_SUMMARY);
        $this->addResource(self::RES_EDIT_MODAL);
        $this->addResource(self::RES_EDIT_ADMINS);

        $this->addResource(self::RES_ADD_FORM);
        $this->addResource(self::RES_EDIT_PERMISSION_FORM);
        $this->addResource(self::RES_EDIT_PERSONAL_DATA_FORM);

        $this->addResource(self::RES_RESET_PASSWORD_HANDLE);
        $this->addResource(self::RES_REMOVE_HANDLE);


        /*
         * Privileges
         */

        // Registrovaný
        $this->allow([
            self::ROLE_AUTHENTICATED,
            self::ROLE_EDITOR
        ], [
            self::RES_EDIT_MODAL
        ]);

        // Editor
        $this->allow([
            self::ROLE_EDITOR
        ], [
            self::RES_USER_MODULE,
            self::RES_SUMMARY,
            self::RES_ADD_FORM,
            self::RES_EDIT_PERMISSION_FORM,
            self::RES_EDIT_PERSONAL_DATA_FORM,
            self::RES_RESET_PASSWORD_HANDLE,
            self::RES_REMOVE_HANDLE
        ]);

        // Admin
        $this->allow([
            self::ROLE_ADMIN
        ], [
            self::RES_EDIT_ADMINS
        ]);


        return parent::create();
    }
}