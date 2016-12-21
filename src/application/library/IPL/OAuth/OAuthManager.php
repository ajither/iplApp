<?php

/**
 * @author     Ajith E R, <ajith@salesx.io>
 * @date       December 19, 2016
 * @brief
 */

namespace library\IPL\OAuth;

use \library\IPL\Email\EmailManager as EmailManager;
use \library\IPL\Common\Logger as Logger;
use \models\Google_Access_Tokens as Google_Access_Tokens;
use \models\Email_Account_Settings as Email_Account_Settings;

class OAuthManager {

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       May 3, 2016
     * @brief      Gets the Authorization code URL
     * @return     URL
     */
    public static function getAuthorizationCodeURL($request) {
        $headers = $request->getHeaders();
        $apiKey = $headers['HTTP_SALESX_API_KEY'][0];
        $sessionToken = $headers['HTTP_SALESX_SESSION_TOKEN'][0];
        $stateJson = '{"API_KEY":"' . $apiKey .
                '","SESSION_TOKEN":"' . $sessionToken . '"}';
        $stateString = strtr(base64_encode($stateJson), '+/=', '-_,');
        $client = self::getGoogleClient();
        $authUrl = $client->createAuthUrl();

        return $authUrl . '&state=' . $stateString;
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       May 4, 2016
     * @brief      Gets the Authorization code URL
     * @return     URL
     */
    public static function handleAuthorizationCallback($request) {
        $queryParams = $request->getQueryParams();
        $authCode = $queryParams['code'];
        $client = self::getGoogleClient();
        $accessToken = $client->authenticate($authCode);

        $client->setAccessToken($accessToken);
        $gmailService = new \Google_Service_Gmail($client);
        $userProfile = $gmailService->users->getProfile('me');

        $data = array();
        $data['user_id'] = $_SESSION["user_id"];
        $data['email_id'] = $userProfile->getEmailAddress();
        $data['google_access_token'] = $accessToken;
        $googleAccessToken = new Google_Access_Tokens();
        $googleAccessToken->addGoogleAccessToken($data);
        $accountId = $googleAccessToken->fetchAccountId($data['email_id'], $data['user_id']);

        $accountData['user_id'] = $_SESSION["user_id"];
        $accountData['type'] = 'GMAIL';
        $accountData['type_id'] = $accountId;
        $emailAccountSetting = new Email_Account_Settings();
        $emailSettingsId = $emailAccountSetting->addAccount($accountData);

        $channel = 'system-notifications-' . $_SESSION["user_id"];
        $pubnubPayload = array();
        $pubnubPayload['type'] = 'gmail-account-added';
        $pushMessage = array();
        $pushMessage['id'] = $emailSettingsId;
        $pushMessage['email_id'] = $data['email_id'];
        $pushMessage['initial_fetch'] = 0;
        $pushMessage['type'] = 'GMAIL';
        $pubnubPayload['message'] = $pushMessage;
        $pubnubPayload = json_encode($pubnubPayload, JSON_NUMERIC_CHECK);
        NotificationManager::pushPubnubNotification($channel, $pubnubPayload);

        EmailManager::initialMailSync();
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       May 4, 2016
     * @brief      Gets google client object
     * @return     Google client object
     */
    public static function getGoogleClient() {
        if (!defined('SCOPES')) {
            define('SCOPES', implode(' ', array(
                \Google_Service_Gmail::MAIL_GOOGLE_COM
                            )
            ));
        }
        $client = new \Google_Client();
        $client->setApplicationName(getenv('GOOGLE.APPLICATIONNAME'));
        $client->setScopes(SCOPES);
        $client->setAuthConfigFile(getenv('APPLICATION.PATH') . '/application/config/google_oauth_client.json');
        $client->setAccessType('offline');
        return $client;
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       May 4, 2016
     * @brief      OAuth2 Authenticate
     * @return     Boolean
     */
    public static function oauth2Authenticate($imap = null, $smtp = null, $email, $accessToken) {
        $authenticateParams = array('XOAUTH2',
            self::constructAuthString($email, $accessToken));
        if ($imap != null) {
            $imap->sendRequest('AUTHENTICATE', $authenticateParams);

            while (true) {
                $response = "";
                $is_plus = $imap->readLine($response, '+', true);
                if ($is_plus) {
                    Logger::error_logger("got an extra server challenge: $response");
                    // Send empty client response.
                    $imap->sendRequest('');
                } else {
                    if (preg_match('/^NO /i', $response) ||
                            preg_match('/^BAD /i', $response)) {
                        Logger::error_logger("got failure response: $response");
                        return false;
                    } else if (preg_match("/^OK /i", $response)) {
                        return true;
                    } else {
                        // Some untagged response, such as CAPABILITY
                    }
                }
            }
        }
        if ($smtp != null) {
            $smtp->_send('EHLO salesx.io');
            $smtp->_send('AUTH XOAUTH2 ' . self::constructAuthString($email, $accessToken));
            $smtp->_startSession();
            while (true) {
                $response = $smtp->_receive(10);
                if (strpos($response, 'Accepted') !== false) {
                    return true;
                }
                if (strpos($response, '334 ') !== false) {
                    return false;
                }
            }
        }
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       May 4, 2016
     * @brief      Construct Auth String
     * @return     Auth String
     */
    public static function constructAuthString($email, $accessToken) {
        return base64_encode("user=$email\1auth=Bearer $accessToken\1\1");
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       May 6, 2016
     * @brief      Refresh Access token
     * @return     Access token
     */
    public static function refreshAuthToken($client, $mailId, $userId) {
        $client->refreshToken($client->getRefreshToken());
        $access_token = $client->getAccessToken();
        $data = array();
        $data['user_id'] = $userId;
        $data['email_id'] = $mailId;
        $data['google_access_token'] = $access_token;
        $googleAccessToken = new Google_Access_Tokens();
        $googleAccessToken->addGoogleAccessToken($data);
        return $access_token;
    }

}
