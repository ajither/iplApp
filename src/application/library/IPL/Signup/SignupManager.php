<?php

/**
 * @author     Ajith E R, <ajith@salesx.io>
 * @date       December 19, 2016
 * @brief
 */

namespace library\IPL\Signup;

use \library\IPL\Hash\HashManager as HashManager;
use \library\IPL\Login\LoginManager as LoginManager;
use \models\User as User;
use \models\Organization as Organization;
use \models\Organization_User_Mapping as Organization_User_Mapping;
use \models\Lead as Lead;
use \models\Activity as Activity;
use \models\Activity_Assigned_Mapping as Activity_Assigned_Mapping;
use \models\Notes as Notes;
use \models\User_Profile as User_Profile;
use \library\IPL\Common\Utils as Utils;
use \models\Pipeline_Stages as Pipeline_Stages;
use \library\IPL\Common\Constants as Constants;
use \models\Lead_Custom_Status_Settings as Lead_Custom_Status_Settings;
use \models\Lead_Custom_Status as Lead_Custom_Status;

class SignupManager {

    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       December 19, 2016
     * @brief      Sign up API
     */
    public static function signupAction($payload) {
        $data['username'] = $payload['user_name'];
        $data['email'] = $payload['user_email'];
        $data['first_name'] = $payload['first_name'];
        $data['last_name'] = $payload['last_name'];
        $data['password'] = HashManager::passwordHash($payload['password']);
        $date['created_date'] = date("Y-m-d H:i:s");
        try {
            $user = new User();
            $user_id = $user->addUser($data);
        } catch (\Exception $e) {
            $response['success'] = "false";
            $response['message'] = "User name already exists";
            return json_encode($response, JSON_NUMERIC_CHECK);
        }
        $response['success'] = "true";
        $response['message'] = "Account Successfully Created";
        return json_encode($response, JSON_NUMERIC_CHECK);
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       August 29, 2016
     * @brief      Change password operation.
     * @param      $payload   Payload data.
     * @return     Json response
     */
    public static function changePassword($payload) {
        $user = new User();
        $userDetails = $user->fetchUserDetails($_SESSION['user_id']);
        if (LoginManager::validateLoginCredentials($userDetails->user_name, $payload['old_password'])) {
            $data['id'] = $_SESSION['user_id'];
            $data['password'] = HashManager::passwordHash($payload['new_password']);
            $user->updateUser($data);
            $response['success'] = "true";
            $response['message'] = "Password changed successfully.";
            return json_encode($response, JSON_NUMERIC_CHECK);
        } else {
            $response['success'] = "false";
            $response['message'] = "Wrong password provided.";
            return json_encode($response, JSON_NUMERIC_CHECK);
        }
    }

}
