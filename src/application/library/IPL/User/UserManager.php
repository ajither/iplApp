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
use models\Answer;
use \models\User as User;
use \models\User_Profile as User_Profile;
use \library\IPL\Hash\HashManager as HashManager;
use \models\Match_Point as Match_Point;
use models\User_Refferal;

class UserManager {

    public static function editUserProfile($payload) {
        $data['user_id'] = $_SESSION['user_id'];
        if(isset($payload['first_name'])){
            $data['first_name'] = $payload['first_name'];
        }
        if (isset($payload['last_name'])){
            $data['last_name'] = $payload['last_name'];
        }
        if (isset($payload['fan_team'])) {
            $data['fanteam'] = $payload['fan_team'];
        }
        $user = new User();
        $user->updateUser($data);

        $userProfileData['user_id'] = $_SESSION['user_id'];
        if (isset($payload['profile_picture'])) {
            $userProfileData['profile_picture'] = $payload['profile_picture'];
        }
        if (isset($payload['phone_number'])){
            $userProfileData['phone_number'] = $payload['phone_number'];
        }
        $userProfileModel = new User_Profile();
        $userProfileModel->editUserProfile($userProfileData);
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

    public static function getTopProfileDetails()
    {
        $topMatchWinner = array();
        $matchPointModel = new Match_Point();
        $matchPoint = $matchPointModel->fetchTopProfileDetails();
        foreach ($matchPoint as $key => $value){
            $user = new User();
            $userDetails = $user->fetchAllDetailsByUserId($value->user_id);
            array_push($topMatchWinner,$userDetails);
        }

        $topRefferalWinner = array();
        $refferalModel = new User_Refferal();
        $refferalPoint = $refferalModel->fetchTopProfileDetails();
        foreach ($refferalPoint as $key => $value){
        $user = new User();
        $userDetails = $user->fetchAllDetailsByUserId($value->user_id);
        array_push($topRefferalWinner,$userDetails);
        }

        $response['success'] = "true";
        $response['topWinner'] = $topMatchWinner;
        $response['topRefferal'] = $topRefferalWinner;
        return json_encode($response, JSON_NUMERIC_CHECK);

    }
}
