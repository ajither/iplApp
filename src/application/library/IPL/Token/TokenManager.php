<?php

/**
 * @author     Nikhil N R, <nikhil@salesx.io>
 * @date       April 24, 2016
 * @brief      This class handles all the operations related to Tokens
 * @details    
 */

namespace library\IPL\Token;

use \library\IPL\Hash\HashManager as HashManager;
use \models\Encryption_Keymap as Encryption_Keymap;
use \library\IPL\Login\LoginManager as LoginManager;

class TokenManager {

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       April 25, 2016
     * @brief      Validates the tokens passed in the headers.
     * @param      $request   request details.
     * @return     Boolean
     */
    public static function validateTokens($request) {
        if ($request->getAttribute('apiname') == 'login' ||
                $request->getAttribute('apiname') == 'signup' ||
                $request->getAttribute('apiname') == 'resetpassword' ||
                $request->getAttribute('apiname') == 'refreshsessiontoken') {
            if (!array_key_exists('HTTP_SALESX_API_KEY', $request->getHeaders())) {
                return false;
            } else {
                $headers = $request->getHeaders();
                return self::validateApiKey($headers['HTTP_SALESX_API_KEY'][0]);
            }
        } else if ($request->getAttribute('apiname') == 'googleauthcallback') {
            $getQueryParams = $request->getQueryParams();
            if (array_key_exists('code', $getQueryParams) &&
                    array_key_exists('state', $getQueryParams)) {
                $stateString = $getQueryParams['state'];
                $stateJson = base64_decode(strtr($stateString, '-_,', '+/='));
                $stateArray = json_decode($stateJson, true);
                $apiKey = $stateArray['API_KEY'];
                $sessionToken = $stateArray['SESSION_TOKEN'];
                $apiKeyValid = self::validateApiKey($apiKey);
                $sessionTokenValid = self::
                        validateSessionToken($sessionToken);
                if ($apiKeyValid && $sessionTokenValid) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else if ($request->getAttribute('apiname') == 'emailopenrecord' ||
                $request->getAttribute('apiname') == 'fetchtwiml' ||
                $request->getAttribute('apiname') == 'incomingcall' ||
                $request->getAttribute('apiname') == 'fetchverifytwiml' ||
                $request->getAttribute('apiname') == 'verifythankyou' ||
                $request->getAttribute('apiname') == 'updatecallstatus') {
            return true;
        } else {
            $headers = $request->getHeaders();
            if (!array_key_exists('HTTP_SALESX_API_KEY', $request->getHeaders())) {
                return false;
            }
            if (!array_key_exists('HTTP_SALESX_SESSION_TOKEN', $request->getHeaders())) {
                return false;
            }
            $apiKeyValid = self::validateApiKey($headers['HTTP_SALESX_API_KEY'][0]);
            $sessionTokenValid = self::
                    validateSessionToken($headers['HTTP_SALESX_SESSION_TOKEN'][0]);
            if ($apiKeyValid && $sessionTokenValid) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       April 25, 2016
     * @brief      Generates the session token based on sx encryption algorithm.
     * @details    A 10 character Encryption key map is generated and one 
     *             character corresponding to one digit is selected. 
     *             That character is the first character(Key index) in the 
     *             session key. Username and password is hashed using SHA256 
     *             after they're concatenated like this [^#username-password#^].
     *             All the characters that are in the index that's a multiple 
     *             of Key Index are segregated as the prefix. User ID is 
     *             converted into alphabets using encryption key map and 
     *             appended to the prefix.The last 10 characters are shuffled 
     *             with the key map and attached as the post-fix.
     *
     *             Format : [key index][Prefix][User Id][Post-fix{Key-map}]
     * @param      $username   Username
     * @param      $password   Password
     * @return     Boolean
     */
    public static function generateSessionToken($username, $password, $encryptionKeyMap = NULL) {
        if ($encryptionKeyMap == NULL) {
            $repeat = false;
            do {
                $encryptionKeyMap = HashManager::generateEncryptionKeymap(10);
                $repeat = false;
                $data = array();
                $data['user_id'] = $_SESSION["user_id"];
                $data['encryption_key'] = $encryptionKeyMap;
                $encKeyMap = new Encryption_Keymap();
                try {
                    $encKeyMap->addEncryptionKeymap($data);
                } catch (\Exception $e) {
                    $repeat = true;
                }
            } while ($repeat == true);
        }
        $encryptionKey = rand(2, 9);
        $sessionToken = $encryptionKeyMap{$encryptionKey};
        $userIdPasswordHash = HashManager::passwordHash("^#" . $username . "-" . $password . "#^");
        $sessionToken = $encryptionKeyMap{$encryptionKey} . $userIdPasswordHash;
        $prefix = "";
        $postfix = "";

        for ($x = 1; $x < strlen($sessionToken); $x++) {
            if ($x % $encryptionKey == 0) {
                $prefix.=$sessionToken{$x};
            } else {
                $postfix.=$sessionToken{$x};
            }
        }
        $prefix = $encryptionKeyMap{$encryptionKey} . $prefix;
        $user_id = $_SESSION["user_id"];
        $mappedUid = "";

        foreach (str_split($user_id) as $digit) {
            $mappedUid.=$encryptionKeyMap{$digit};
        }
        $preKeyedHash = $prefix . $mappedUid . $postfix;
        $firsthalf = substr($preKeyedHash, 0, -10);
        $secondhalf = substr($preKeyedHash, -10);
        $keyedSecondHalf = "";
        for ($j = 0; $j < 10; $j++) {
            $keyedSecondHalf.=$secondhalf{$j} . $encryptionKeyMap{$j};
        }

        $sessionToken = $firsthalf . $keyedSecondHalf;
        return $sessionToken;
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       April 27, 2016
     * @brief      Refresh Session Token API
     * @param      $username   Username
     * @param      $password   Password
     * @return     Session token response json.
     */
    public static function refreshSessionToken($username, $password) {
        if (!LoginManager::validateLoginCredentials($username, $password)) {
            $response['success'] = "false";
            $response['message'] = "Incorrect credentials";
            return json_encode($response, JSON_NUMERIC_CHECK);
        } else {
            $session_token = self::generateSessionToken($username, $password);
            $response['success'] = "true";
            $response['session_token'] = $session_token;
            return json_encode($response, JSON_NUMERIC_CHECK);
        }
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       April 27, 2016
     * @brief      Validates the session token.
     * @param      $username   Username
     * @param      $password   Password
     * @return     Session token response json.
     */
    public static function validateSessionToken($sessionToken) {
        $mixedKeymapHash = substr($sessionToken, -20);
        $encryptionKeyMap = "";
        $secondhalf = "";
        for ($i = 0; $i < 20; $i = $i + 2) {
            $secondhalf.= $mixedKeymapHash{$i};
        }
        for ($i = 1; $i < 20; $i = $i + 2) {
            $encryptionKeyMap.= $mixedKeymapHash{$i};
        }
        $encryptionKey = strpos($encryptionKeyMap, $sessionToken{0});

        if ($encryptionKey == null) {
            return false;
        }

        $prefixLength = floor(64 / $encryptionKey);
        $postfixlength = 64 - $prefixLength;
        $prefix = substr($sessionToken, 1, $prefixLength);
        $middleHalf = substr($sessionToken, $prefixLength + 1, -20);
        $secondhalf = $middleHalf . $secondhalf;
        $withUid = $prefix . $secondhalf;
        $withoutUid = substr($withUid, -$postfixlength);
        $idLength = strlen($withUid) - strlen($withoutUid) - $prefixLength;
        $idAlpha = substr($secondhalf, 0, $idLength);
        $id = "";
        foreach (str_split($idAlpha) as $char) {
            $id .= strpos($encryptionKeyMap, $char);
        }

        $encryptionKeymapModel = new Encryption_Keymap();
        $uidDb = $encryptionKeymapModel->fetchUidWithEncryptionKeymap($encryptionKeyMap);

        if ($id == $uidDb) {
            $_SESSION["user_id"] = $id;
            $_SESSION['access_log_string'].='--[User-id:' . $_SESSION["user_id"] . ']';
            return true;
        } else {
            return false;
        }
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       May 4, 2016
     * @brief      Validates the api key.
     * @param      $apiKey   Api Key
     * @return     Boolean.
     */
    public static function validateApiKey($apiKey) {
        if ($apiKey == getenv('API.KEY.IOS')) {
            $_SESSION['access_log_string'].='--[IOS]';
            $_SESSION['accessing_platform'] = 'IOS';
            return true;
        } else
        if ($apiKey == getenv('API.KEY.ANDROID')) {
            $_SESSION['access_log_string'].='--[ANDROID]';
            $_SESSION['accessing_platform'] = 'ANDROID';
            return true;
        } else
        if ($apiKey == getenv('API.KEY.WEB')) {
            $_SESSION['access_log_string'].='--[WEB]';
            $_SESSION['accessing_platform'] = 'WEB';
            return true;
        } else
            return false;
    }

}
