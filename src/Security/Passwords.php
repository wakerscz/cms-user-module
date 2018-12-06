<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\UserModule\Security;


class Passwords
{
    const COSTS = 12;


    /**
     * @param string $password
     * @param string $hash
     * @return bool
     */
    public static function verify(string $password, string $hash) : bool
    {
        return password_verify($password, $hash);
    }


    /**
     * @param $password
     * @return string
     */
    public static function hash($password) : string
    {
        return password_hash($password, PASSWORD_ARGON2I, ['costs' => self::COSTS]);
    }
}