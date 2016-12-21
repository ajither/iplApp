<?php

/**
 * @author     Ajith E R, <ajith@salesx.io>
 * @date       December 19, 2016
 * @brief
 */

namespace library\IPL\Hash;

class HashManager {

    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       December 19, 2016
     * @brief
     */
    public static function passwordHash($password) {
        return hash('SHA256', $password);
    }

    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       December 19, 2016
     * @brief
     */
    public static function passwordCheck($password, $hash) {
        if (self::passwordHash($password) == $hash) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       December 19, 2016
     * @brief
     */
    public static function generateEncryptionKeymap($length = 10) {
        if ($length > 52) {
            throw new \Exception("Impossible Keymap length", 500, null);
        }
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $char = $characters[rand(0, strlen($characters) - 1)];
            $randomString .= $char;
            $characters = str_replace($char, "", $characters);
        }
        return $randomString;
    }

}
