<?php

/**
 * @author     Nikhil N R, <nikhil@salesx.io>
 * @date       August 25, 2016
 * @brief      This class handles all the operations related to User
 * @details    
 */

namespace library\IPL\User;

use \libphonenumber\PhoneNumberUtil as PhoneNumberUtil;
use \library\IPL\Common\Utils as Utils;
use \library\IPL\Email\EmailManager as EmailManager;
use \models\User as User;
use \models\User_Profile as User_Profile;
use \library\IPL\Hash\HashManager as HashManager;
class UserManager {

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       August 25, 2016
     * @brief      Edits the user profile.
     * @param      $request   request details.
     * @return     Boolean
     */
    public static function editUserProfile($payload) {
        $userProfileModel = new User_Profile();
        if (isset($payload['salesx_number'])) {
            $payload['number_verified'] = 0;
            try {
                $phoneUtils = \libphonenumber\PhoneNumberUtil::getInstance();
                $phoneNumber = $phoneUtils->parse("+" . $payload['salesx_number'], null);
                $numberType = $phoneUtils->getNumberType($phoneNumber);
                if ($numberType == 10) {
                    $response['success'] = "false";
                    $response['message'] = "Invalid phone number.";
                    return json_encode($response, JSON_NUMERIC_CHECK);
                }
            } catch (\Exception $e) {
                $response['success'] = "false";
                $response['message'] = "Invalid phone number.";
                return json_encode($response, JSON_NUMERIC_CHECK);
            }
        }
        $payload['id'] = $_SESSION["user_id"];
        $userProfileModel->editUserProfile($payload);
        $response['success'] = "true";
        $response['message'] = "User profile edited successfully.";
        return json_encode($response, JSON_NUMERIC_CHECK);
    }


    public static function fetchUserDetails() {
        $user_id= $_SESSION['user_id'];
        $user = new User();
        $userDetails = $user->fetchAllDetailsByUserId($user_id);
        $response['success'] = "true";
        $response['user_details'] = $userDetails;
        return json_encode($response, JSON_NUMERIC_CHECK);
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       October 19, 2016
     * @brief      User access control
     * @param      $request   request details.
     * @return     $response
     */
    public static function validateRoles($routeRoles) {
        if (empty($routeRoles)) {
            return true;
        }
        $userModel = new User();
        $userRole = $userModel->fetchUserRole($_SESSION["user_id"]);

        if (isset($routeRoles['role'])) {
            if (is_array($routeRoles['role'])) {
                if (in_array($userRole->role_id, $routeRoles['role'])) {
                    return true;
                }
            } else {
                if ($userRole->role_id == $routeRoles['role']) {
                    return true;
                }
            }
        }
        return false;
    }
}
