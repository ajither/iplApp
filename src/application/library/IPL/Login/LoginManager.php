<?php

/**
 * @author     Ajith E R, <ajith@salesx.io>
 * @date       December 19, 2016
 * @brief      This class handle all operations related to Login
 */
namespace library\IPL\Login;

use \library\IPL\Token\TokenManager as TokenManager;
use \library\IPL\Hash\HashManager as HashManager;
use \models\User as User;
use \models\Encryption_Keymap as Encryption_Keymap;

class LoginManager {

    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       December 19, 2016
     * @brief      Login Actions
     */
    public static function loginAction($payload) {
        if (self::validateLoginCredentials($payload['user_name'], $payload['user_password'])) {
            $encryptionKeyMapModel = new Encryption_Keymap();
            $encryptionKeyMap = $encryptionKeyMapModel->fetchEncryptionKeymapWithUid($_SESSION["user_id"]);
            $sessionToken = TokenManager::generateSessionToken($payload['user_name'], $payload['user_password'], $encryptionKeyMap);
            $response['success'] = "true";
            $response['sessionToken'] = $sessionToken;
            return json_encode($response, JSON_NUMERIC_CHECK);
        }else {
            $response['success'] = "false";
            $response['message'] = "Incorrect credentials";
            return json_encode($response, JSON_NUMERIC_CHECK);
        }
    }

    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       December 19, 2016
     * @brief      Validate Credentials
     */
    public static function validateLoginCredentials($userName, $password) {
        $user = new User();
        $userDetails = $user->fetchDetailsByUsername($userName);
        if ($userDetails !== null && HashManager::passwordCheck($password, $userDetails->password)) {
            $_SESSION["user_id"] = $userDetails->user_id;
            $_SESSION["user_user_name"] = $userDetails->user_name;
            return true;
        } else {
            return false;
        }
    }

    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       December 19, 2016
     * @brief      Logout Action
     */
    public static function logoutAction() {
        $encKeymapModel = new Encryption_Keymap();
        $encKeymapModel->deleteKeymap($_SESSION["user_id"]);
        $response['success'] = "true";
        $response['message'] = "User logged out";
        return json_encode($response, JSON_NUMERIC_CHECK);
    }

}
