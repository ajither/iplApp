<?php

/**
 * @author     Ajith E R, <ajith@salesx.io>
 * @date       December 19, 2016
 * @brief
 */

namespace library\IPL\Signup;

use \library\IPL\Hash\HashManager as HashManager;
use \library\IPL\Login\LoginManager as LoginManager;
use \models\Match_Point;
use \models\User as User;
use \models\User_Profile as User_Profile;
use \models\User_Refferal;
use models\User_Total_Point;

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
        if(isset($payload['fan_team'])){
            $data['fanteam'] = $payload['fan_team'];
        }
        $data['password'] = HashManager::passwordHash($payload['password']);
        $date['created_date'] = date("Y-m-d H:i:s");
        if((isset($payload['refferal_code'])) && ($payload['refferal_code'] != '')){
            $user_refferal = new User_Refferal();
            $refferalData = $user_refferal->checkRefferalCode($payload['refferal_code']);
            if($refferalData == null){
                $response['success'] = "false";
                $response['message'] = "Refferal Code Not Found.";
                return json_encode($response, JSON_NUMERIC_CHECK);
            }
        }
        try {
            $user = new User();
            $user_id = $user->addUser($data);
        } catch (\Exception $e) {
            $response['success'] = "false";
            $response['message'] = "User name already exists";
            return json_encode($response, JSON_NUMERIC_CHECK);
        }
        $totalPoint['user_id'] = $user_id;
        $totalPoint['totalpoint'] = 0;
        $userTotalPointModel = new User_Total_Point();
        $userTotalPointModel->updateTotalPoint($totalPoint);
        
        if(isset($payload['refferal_code'])){
            self::addRefferalPoint($payload['refferal_code']);
        }
        if(isset($payload['oauth_provider'])){
            $profileData['oauth_provider'] = $payload['oauth_provider'];
        }
        if (isset($payload['oauth_uid'])){
            $profileData['oauth_uid'] = $payload['oauth_uid'];
        }
        if (isset($payload['gender'])){
            $profileData['gender'] = $payload['gender'];
        }
        if (isset($payload['locale'])){
            $profileData['locale'] = $payload['locale'];
        }
        if (isset($payload['profile_picture'])){
            $profileData['profile_picture'] = $payload['profile_picture'];
        }
        $profileData['phone_number'] = $payload['phone_number'];
        $profileData['user_id'] = $user_id;
        $code = self::generateRefferalCode(4);
        $profileData['refferal_code'] = 'IPL'.strtoupper($code);
        $user_profile = new User_Profile();
        $user_profile->addUserProfile($profileData);

        $refferal['user_id'] = $user_id;
        $refferal['refferal_code'] = $profileData['refferal_code'];
        $refferal['refferal_point'] = 0;
        $refferal['refferal_users_count'] = 0;
        $user_refferal = new User_Refferal();
        $user_refferal->addUserRefferal($refferal);

        $matchPoint['user_id'] = $user_id;
        $matchPoint['matchpoint'] = 0;
        $matchPointModel = new Match_Point();
        $matchPointModel->insertMatchpoint($matchPoint);

        $response['success'] = "true";
        $response['message'] = "Account Successfully Created";
        return json_encode($response, JSON_NUMERIC_CHECK);
    }

    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       December 19, 2016
     * @brief      Sign up API
     */
    public static function changePassword($payload) {
        $user = new User();
        $userDetails = $user->fetchUserDetails($_SESSION['user_id']);
        if (LoginManager::validateLoginCredentials($userDetails->user_name, $payload['old_password'])) {
            $data['user_id'] = $_SESSION['user_id'];
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

    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       December 19, 2016
     * @brief      Generate User Refferal Codes.
     */
    private static function generateRefferalCode($length)
    {
        $str = "";
        $characters = array_merge(range('A','Z'), range('a','z'), range('0','9'));
        $max = count($characters) - 1;
        for ($i = 0; $i < $length; $i++) {
            $rand = mt_rand(0, $max);
            $str .= $characters[$rand];
        }
        $user_refferal = new User_Refferal();
        $refferal_code = 'IPL'.strtoupper($str);
        $refferal = $user_refferal->checkRefferalCode($refferal_code);
        if($refferal == null){
            return $str;
        }
        else{
        self::generateRefferalCode(4);
        }
    }

    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       December 22, 2016
     * @brief      Add refferal Points to user.
     */
    private static function addRefferalPoint($refferal_code)
    {
        $user_refferal = new User_Refferal();
        $refferal = $user_refferal->checkRefferalCode($refferal_code);
        $refferal = json_decode(json_encode($refferal),TRUE);
        $refferalCode['refferal_point'] = $refferal['refferal_point']+2;
        $refferalCode['refferal_users_count'] = $refferal['refferal_users_count']+1;
        $refferalCode['refferal_code'] = $refferal_code;
        $user_refferal = new User_Refferal();
        $user_refferal->updateUserRefferal($refferalCode);

        $matchPointModel = new Match_Point();
        $matchPoint = $matchPointModel->getPoint($refferal['user_id']);
        $totalPoint['totalpoint'] = $refferalCode['refferal_point']+$matchPoint;
        $totalPoint['user_id'] = $refferal['user_id'];
        $totalPointModel = new User_Total_Point();
        $totalPointModel->updateTotalPoint($totalPoint);
    }

    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       December 22, 2016
     * @brief      Send Reset Code.
     */
    public static function resetUserPassword($payload)
    {
        $user = new User();
        $userDetails = $user->fetchDetailsByEmail($payload['user_email']);
        if($userDetails != null){
            $data['email'] = $payload['user_email'];
            $data['password'] = HashManager::passwordHash($payload['user_password']);
            $user = new User();
            $user->editUser($data);
            $response['success'] = "true";
            $response['message'] = "Password Reset Successfully";
            return json_encode($response, JSON_NUMERIC_CHECK);
        }else{
            $response['success'] = "false";
            $response['message'] = "Email id not Found.";
            return json_encode($response, JSON_NUMERIC_CHECK);
        }

    }

}
