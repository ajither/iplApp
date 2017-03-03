<?php

/**
 * @author     Ajith E R, <ajith@salesx.io>
 * @date       December 19, 2016
 * @brief      This class controlls all api calls.
 * @version    v1.0
 * @details    API calls are directed towards a method with the same name as the
 *             API.

 */
use \library\IPL\Signup\SignupManager as SignupManager;
use \library\IPL\Login\LoginManager as LoginManager;
use \library\IPL\Email\SXMailMessage as SXMailMessage;
use \library\IPL\Email\SendGoogleEmail as SendGoogleEmail;
use \library\IPL\Common\Logger as Logger;
use \library\IPL\Common\Validations as Validations;
use \models\Google_Access_Tokens as Google_Access_Tokens;
use \library\IPL\User\UserManager as UserManager;
use \library\IPL\Match\MatchManager as MatchManager;
use \library\IPL\Answer\AnswerManager as AnswerManager;
use \library\IPL\Reward\RewardManager as RewardManager;

class ApiController {

    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       December 19, 2016
     * @brief      Sign up API
     */
    public function signup($request) {
        $payload = $request->getParsedBody();
        $expectedFields = ["user_email", "password", "first_name", "last_name"];
        $result = Validations::validateMandatoryFields($expectedFields, $payload);
        if (!$result['status']) {
            return json_encode($result['body'], JSON_NUMERIC_CHECK);
        }

        return SignupManager::signupAction($payload);
    }

    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       December 19, 2016
     * @brief      Login API
     */
    public function login($request) {
        $payload = $request->getParsedBody();
        $expectedFields = ["user_name", "user_password", "user_ip"];
        $result = Validations::validateMandatoryFields($expectedFields, $payload);
        if (!$result['status']) {
            return json_encode($result['body'], JSON_NUMERIC_CHECK);
        }
        return LoginManager::loginAction($payload);
    }

    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       December 22, 2016
     * @brief      Reset User Password.
     */
    public function resetPassword($request) {
        $payload = $request->getParsedBody();
        $expectedFields = ["user_email"];
        $result = Validations::validateMandatoryFields($expectedFields, $payload);
        if (!$result['status']) {
            return json_encode($result['body'], JSON_NUMERIC_CHECK);
        }

        return SignupManager::resetUserPassword($payload);
    }

    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       December 22, 2016
     * @brief      Reset User Password.
     */
    public function userDetails($request) {
        return UserManager::fetchUserDetails();
    }

    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       December 22, 2016
     * @brief      Reset User Password.
     */
    public function matchUpdate($request) {
        return MatchManager::getMatchDetails();
    }

    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       December 22, 2016
     * @brief      Submit Answer.
     */
    public function matchAnswer($request) {
        $payload = $request->getParsedBody();
        $expectedFields = ["answer","matchNo"];
        $result = Validations::validateMandatoryFields($expectedFields, $payload);
        if (!$result['status']) {
            return json_encode($result['body'], JSON_NUMERIC_CHECK);
        }

        return AnswerManager::updateMatchAnswer($payload);
    }

    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       December 22, 2016
     * @brief      getTopProfileDetails.
     */
    public function topProfile($request) {
        return UserManager::getTopProfileDetails();
    }

    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       December 22, 2016
     * @brief      getTopProfileDetails.
     */
    public function matchSchedule($request) {
        return MatchManager::getMatchSchedule();
    }

    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       December 22, 2016
     * @brief      getTopProfileDetails.
     */
    public function matchWinnerUpdate($request) {
        $payload = $request->getParsedBody();
        $expectedFields = ["answer","matchNo"];
        $result = Validations::validateMandatoryFields($expectedFields, $payload);
        if (!$result['status']) {
            return json_encode($result['body'], JSON_NUMERIC_CHECK);
        }

        return AnswerManager::updateMatchScore($payload);
    }

    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       December 22, 2016
     * @brief      getTopProfileDetails.
     */
    public function editProfile($request) {
	$payload = $request->getParsedBody();
        return UserManager::editUserProfile($payload);
    }

    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       December 22, 2016
     * @brief      getTopProfileDetails.
     */
    public function updateToken($request) {
        $payload = $request->getParsedBody();
        $expectedFields = ["fcm_token"];
        $result = Validations::validateMandatoryFields($expectedFields, $payload);
        if (!$result['status']) {
            return json_encode($result['body'], JSON_NUMERIC_CHECK);
        }
        return UserManager::fcmTokenUpdate($payload);
    }

    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       December 22, 2016
     * @brief      getTopProfileDetails.
     */
    public function usertotalpoint($request) {
        return UserManager::getTotalPoint();
    }

    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       December 22, 2016
     * @brief      getTopProfileDetails.
     */
    public function redeemrequest($request) {
        return RewardManager::updateRedeemRequest();
    }

    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       December 19, 2016
     * @brief      Error code
     */
    public function error($code, $data = NULL) {
        $message = [
            400 => "Bad Request - Request does not have a valid format, all required parameters, etc.",
            401 => "Unauthorized Access - No currently valid authorization has been made.",
            403 => "Forbidden Access - Access to this service or resource is forbidden with the given authorization.",
            404 => "Not Found - Service or resource was not found",
            500 => "System Error - Specific reason is included in the error message"
        ];
        $error['code'] = $code;
        $error['message'] = "";
        if (array_key_exists($code, $message)) {
            $error['message'] = $message[$code];
        }
        if ($data != null) {
            if (strlen($error['message']) == 0) {
                $error['message'] = $data->getMessage();
                $error['trace'] = $data->getTrace();
            }
        }
        $response['error'] = $error;
        $response['error']['debug_backtrace'] = debug_backtrace();
        Logger::error_logger($response);
        if (!$code == 401) {
            $to = explode(",", getenv('SYSTEM.ERROR.MAIL.RECIPIENTS'));
            $mailMessage = new SXMailMessage();
            $mailMessage->setFromName('IplGuess');
            $mailMessage->setFrom(getenv('SYSTEM.ERROR.MAIL'));
            $mailMessage->setTo($to);
            $mailMessage->setSubject('SalesX Error - ' . $error['message']);
            $mailMessage->setIsHTML(true);
            if (isset($error['trace'])) {
                $mailMessage->setBody('<html><b>' . json_encode($error['trace']) . '</b></html>');
            } else {
                $mailMessage->setBody('Check error logs for further details.');
            }
            $googleAccessTokenModel = new Google_Access_Tokens();
            $googleToken = $googleAccessTokenModel->fetchGoogleAccessToken(getenv('SYSTEM.USER.ID'), getenv('SYSTEM.ERROR.MAIL'));
            SendGoogleEmail::sendEmail($mailMessage, getenv('SYSTEM.USER.ID'), $googleToken);
        }
        return json_encode($response, JSON_NUMERIC_CHECK);
    }

}
