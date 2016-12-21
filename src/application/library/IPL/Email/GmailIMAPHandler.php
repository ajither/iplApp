<?php

/**
 * @author     Nikhil N R, <nikhil@salesx.io>
 * @date       May 5, 2016
 * @brief      This class is used receiving Google emails
 */

namespace library\SX\Email;

use \models\Google_Access_Tokens as Google_Access_Tokens;
use \models\Email_Content as Email_Content;
use \models\Email_Account_Settings as Email_Account_Settings;
use \models\Organization_User_Mapping as Organization_User_Mapping;
use \library\SX\OAuth\OAuthManager as OAuthManager;
use \library\SX\Email\Utils as EmailUtils;
use \library\SX\Common\AsynchronousOperations as AsynchronousOperations;
use \library\SX\Common\Logger as Logger;
use \Zend\Mail\Protocol\Imap as Imap;
use \Zend\Mail\Storage\Imap as Storage;

class GmailIMAPHandler {

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       May 5, 2016
     * @brief      Fetches all emails from google.
     */
    public static function syncMailDirectory(Storage $storage, $mailAddress, $directory, $accesArray, $imap, $userId, $searchParams = null, $skip = null, $limit = null, $type, $mailUserData = null, $mailReminder = -1) {
        $sxImapId = $accesArray['id'];
        $sxAccountId = $accesArray['sx_account_id'];
        $emailContentModel = new Email_Content("Mailbox");
        $attachmentUserDir = getenv('APPLICATION.PATH') . "/application/files/emailattachments/" . $userId;
        if (!file_exists($attachmentUserDir)) {
            mkdir($attachmentUserDir);
        }
        $attachmentDir = $attachmentUserDir . "/" . $sxImapId;
        if (!file_exists($attachmentDir)) {
            mkdir($attachmentDir);
        }
        $attachmentDir = $attachmentDir . "/" . $directory;
        if (!file_exists($attachmentDir)) {
            mkdir($attachmentDir);
        }
        Logger::debug_logger("Fetching mail data from " . $directory);

        $mailsIds = array();
        if ($searchParams == null) {
            Logger::debug_logger("Fetching all mails");
            $mailsIds = $imap->search(array("ALL"));
        } else {
            $searchQuery = Utils::generateSearchQuery($searchParams);
            foreach ($searchQuery as $query) {
                Logger::debug_logger("Searching..");
                Logger::debug_logger($query);
                $searchResult = $imap->search(array($query));
                if (count($searchResult) > 0) {
                    foreach ($searchResult as $key => $searchedId) {
                        array_push($mailsIds, $searchedId);
                    }
                }
            }
            $mailsIds = array_unique($mailsIds);
        }
        Logger::debug_logger($mailsIds);
        if (count($mailsIds) == 0) {
            Logger::debug_logger("No revelant mails present in the server.");
            return;
        }
        $initialSync = false;
        if ($limit != null) {
            $initialSync = true;
            $mailArrays = Utils::trimMailIdArray($mailsIds, $skip, $limit, $mailAddress, $userId, $directory);
            $mailsIds = $mailArrays['trimmed_array'];
            $rejectArray = $mailArrays['rejected_array'];
        }
        Logger::debug_logger("Fetching server metadata");

        $mailContent = new Email_Content('Email_Content');
        $emailStoredMetaArray = $mailContent->fetchEmailMetadata($mailAddress, $userId, $directory);

        $deletedArray = array();
        $newArray = array();
        $fetchedArray = array();
        $storedArray = array();

        foreach ($mailsIds as $fetchedKey => $fetchedValue) {
            $fetchedArray[$fetchedKey] = $fetchedValue;
        }

        if ($emailStoredMetaArray == null) {
            EmailUtils::addEmailMetadata(array(), $mailAddress, $userId, $directory);
            $newArray = $fetchedArray;
        } elseif ($initialSync) {
            Logger::debug_logger("Initial Sync...");
            $newArray = $fetchedArray;
            $storedArray = $rejectArray;
        } else {
            foreach ($emailStoredMetaArray as $storedKey => $storedValue) {
                $storedArray[$storedKey] = $storedValue;
            }
            $indexCorrectionResult = EmailUtils::gmailArrayIndexCorrection($storedArray, $fetchedArray);
            $deletedArray = $indexCorrectionResult['DeletedArray'];
            if ($searchParams != null && isset($searchParams['sentmail_uid'])) {
                $deletedArray = array();
            }
            $newArray = $indexCorrectionResult['NewArray'];
        }

        if (sizeof($deletedArray) == 0 && sizeof($newArray) == 0) {
            Logger::debug_logger("Up to date >>" . $directory);
            return;
        }

        if (sizeof($deletedArray) > 0) {
            $storedArray = array_diff($storedArray, $deletedArray);
            $mailMetaArray = [
                "user_id" => (Integer) $userId,
                "sx_imap_id" => $mailAddress,
                "directory" => $directory,
                "mailKeyArray" => $storedArray];
            $mailContent->updateEmailArray($mailMetaArray);

            foreach ($deletedArray as $deleted_mid) {
                $del_mail_details = $emailContentModel->getMailDetails($deleted_mid, $directory);
                foreach (json_decode($del_mail_details->attachments, true) as $attachmentPath) {
                    unlink($attachmentPath);
                }
            }
            $emailContentModel->deleteMailFromSx($directory, $deletedArray);
        }

        end($newArray);
        $lastKey = key($newArray);
        $organizationModel = new Organization_User_Mapping();
        $orgId = $organizationModel->fetchOrganizationUserMapping($userId);
        for ($i = $lastKey; $i >= 0; $i--) {
            try {
                if (array_key_exists($i, $newArray)) {
                    Logger::debug_logger("Importing ->>>>" . $i . "=>" . $newArray[$i]);
                    $mail = $storage->getMessage($newArray[$i]);
                    $storageUid = $storage->getUniqueId($newArray[$i]);
                    $emailArray = EmailUtils::generateMailArray($mail, $directory, $attachmentDir, $storageUid, $userId, $type);
                    try {
                        if (isset($emailArray['flags']['\Recent'])) {
                            unset($emailArray['flags']['\Recent']);
                        }
                        $storage->setFlags($newArray[$i], $emailArray['flags']);
                    } catch (\Exception $e) {
                        Logger::error_logger($e->getMessage());
                    }
                    if ($searchParams != null && isset($searchParams['sentmail_uid'])) {
                        $emailArray['sxuid'] = $searchParams['sentmail_uid'];
                    } else {
                        $emailArray['sxuid'] = uniqid();
                    }
                    $emailArray['sx_account_id'] = (Integer) $sxAccountId;
                    if ($mailUserData != null) {
                        $emailArray['mail_user_data'] = $mailUserData;
                    } else {
                        $mailUserData = Utils::fetchMailUserData($emailArray, $orgId);
                        if ($mailUserData != null) {
                            $emailArray['mail_user_data'] = $mailUserData;
                            $mailUserData = null;
                        }
                    }
                    if ($mailReminder != -1) {
                        Logger::debug_logger("Adding reminder Data");
                        EmailUtils::addMailReminderData($mailReminder, $emailArray);
                    }
                    if (isset($emailArray['In-Reply-To'])) {
                        EmailUtils::updateReplyReminder($emailArray);
                    }
                    $emailContentModel->saveEmail($emailArray);
                    array_push($storedArray, $newArray[$i]);
                    $mailMetaArray = [
                        "user_id" => (Integer) $userId,
                        "sx_imap_id" => $mailAddress,
                        "directory" => $directory,
                        "mailKeyArray" => $storedArray];
                    $mailContent->updateEmailArray($mailMetaArray);
                }
            } catch (\Exception $e) {
                Logger::error_logger($e->getMessage());
                Logger::error_logger(debug_backtrace());
            }
        }
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       May 21, 2016
     * @brief      Fetches all mail boxes.
     */
    public static function syncAllMail($emailAddress, $sxImapId, $userId, $fork = false, $searchParams = null) {
        Logger::debug_logger("Sync all mail..");
        $googleAuthCodeModel = new Google_Access_Tokens();

        $googleToken = $googleAuthCodeModel->fetchGoogleAccessToken($userId, $emailAddress, $fork);
        $access_token = $googleToken['google_access_token'];

        $emailAccountSettingsModel = new Email_Account_Settings();
        $gmailId['sx_account_id'] = $emailAccountSettingsModel->fetchAccountDetails('GMAIL', $sxImapId);
        $gmailId['id'] = $sxImapId;

        $client = OAuthManager::getGoogleClient();
        $client->setAccessToken($access_token);

        if ($client->isAccessTokenExpired()) {
            Logger::debug_logger("Google Access token Expired, Refreshing..");
            try {
                $access_token = OAuthManager::refreshAuthToken($client, $emailAddress, $userId);
            } catch (\Google_Auth_Exception $e) {
                Logger::error_logger("Authentication Error: " . $e->getMessage());
                return;
            }
        }

        $accesArray = json_decode($access_token, true);
        $imap = new Imap(getenv('IMAP.ADDRESS.GOOGLE'), getenv('IMAP.PORT.GOOGLE'), 'ssl');


        if (OAuthManager::oauth2Authenticate($imap, null, $emailAddress, $accesArray['access_token'])) {
            Logger::debug_logger("Google authenticaion successsful..");
            $storage = new Storage($imap);
            foreach ($imap->listMailbox() as $name => $folder) {
                if (!($name == '[Gmail]')) {
                    Logger::debug_logger("selecting " . $name);
                    try {
                        $storage->selectFolder($name);
                    } catch (\Exception $e) {
                        Logger::error_logger($e->getMessage());
                    }
                    $name = str_replace("/", "", $name);
                    $name = str_replace("[", "", $name);
                    $name = str_replace("]", "", $name);
                    Logger::debug_logger($name);
                    if ($name == "INBOX" || $name == "GmailSent Mail" || $name == "GmailDrafts") {
                        self::syncMailDirectory($storage, $emailAddress, $name, $gmailId, $imap, $userId, $searchParams, null, null, 'GMAIL');
                    }
                }
            }
        }
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       October 19, 2016
     * @brief      Updates the incoming emails of all synched mails ids in the
     *             system.
     * @param      $batchSize   Batch size of each thread.
     * @return     Json response
     */
    public function updateIncomingEmail($batchSize) {
        $googleModel = new Google_Access_Tokens();
        $googleIds = $googleModel->fetchAllData();
        $batchData = EmailUtils::generateBatchData($googleIds, $batchSize);
        AsynchronousOperations::fetchIncomingGmail($batchData);
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       October 24, 2016
     * @brief      Updates the sent mail
     * @param      $uniqueId   Unique Id.
     */
    public static function updateGoogleSentMail($arguments) {
        $uniqueId = $arguments['unique_id'];
        $emailAddress = $arguments['from'];
        $accesArray = $arguments['access_array'];
        $userId = $arguments['user_id'];
        $sxImapId = $arguments['token_id'];
        $sxAccountId = $arguments['sx_account_id'];
        $mailUserData = $arguments['mail_user_data'];
        if (isset($arguments['mail_reminder'])) {
            $mailReminder = $arguments['mail_reminder_date'];
        } else {
            $mailReminder = -1;
        }
        $imap = new Imap(getenv('IMAP.ADDRESS.GOOGLE'), getenv('IMAP.PORT.GOOGLE'), 'ssl');

        if (OAuthManager::oauth2Authenticate($imap, null, $emailAddress, $accesArray['access_token'])) {
            $storage = new Storage($imap);

            Logger::debug_logger("selecting [Gmail]/Sent Mail");
            try {
                $storage->selectFolder('[Gmail]/Sent Mail');
            } catch (\Exception $e) {
                Logger::error_logger($e->getMessage());
            }
            $name = '[Gmail]/Sent Mail';
            $name = str_replace("/", "", $name);
            $name = str_replace("[", "", $name);
            $name = str_replace("]", "", $name);

            $searchParams['sentmail_uid'] = $uniqueId;
            $sxIdArray['id'] = $sxImapId;
            $sxIdArray['sx_account_id'] = $sxAccountId;
            self::syncMailDirectory($storage, $emailAddress, $name, $sxIdArray, $imap, $userId, $searchParams, null, null, 'GMAIL', $mailUserData, $mailReminder);
        }
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       October 26, 2016
     * @brief      Initial Mail Sync operation
     * @param      $userId   User Id
     */
    public static function initialMailSync($userId, $accesArray) {
        $emailId = $accesArray['email_id'];
        $access_token = $accesArray['google_access_token'];
        $client = OAuthManager::getGoogleClient();
        $client->setAccessToken($access_token);

        if ($client->isAccessTokenExpired()) {
            Logger::debug_logger("Google Authentication token expired. Refreshing token.");
            $access_token = OAuthManager::refreshAuthToken($client, $accesArray['email_id'], $userId);
        }
        $tokenArray = json_decode($access_token, true);

        $imap = new Imap(getenv('IMAP.ADDRESS.GOOGLE'), getenv('IMAP.PORT.GOOGLE'), 'ssl');

        if (OAuthManager::oauth2Authenticate($imap, null, $emailId, $tokenArray['access_token'])) {
            $storage = new Storage($imap);

            Logger::debug_logger("selecting [Gmail]/Sent Mail");
            try {
                $storage->selectFolder('[Gmail]/Sent Mail');
            } catch (\Exception $e) {
                Logger::error_logger($e->getMessage());
            }
            $name = '[Gmail]/Sent Mail';
            $name = str_replace("/", "", $name);
            $name = str_replace("[", "", $name);
            $name = str_replace("]", "", $name);

            $googleTokenModel = new Google_Access_Tokens();
            $googleTokenModel->syncLockAccount($emailId, $userId, 1);
            self::syncMailDirectory($storage, $emailId, $name, $accesArray, $imap, $userId, null, 0, 100, 'GMAIL');
            $googleTokenModel->syncLockAccount($emailId, $userId, 0);
            Logger::debug_logger("selecting INBOX");
            try {
                $storage->selectFolder('INBOX');
            } catch (\Exception $e) {
                Logger::error_logger($e->getMessage());
            }
            $name = 'INBOX';
            $name = str_replace("/", "", $name);
            $name = str_replace("[", "", $name);
            $name = str_replace("]", "", $name);
            $googleTokenModel->syncLockAccount($emailId, $userId, 1);
            self::syncMailDirectory($storage, $emailId, $name, $accesArray, $imap, $userId, null, 0, 500, 'GMAIL');
            $googleTokenModel->syncLockAccount($emailId, $userId, 0);
            Logger::debug_logger("selecting [Gmail]/Drafts");
            try {
                $storage->selectFolder('[Gmail]/Drafts');
            } catch (\Exception $e) {
                Logger::error_logger($e->getMessage());
            }
            $name = '[Gmail]/Drafts';
            $name = str_replace("/", "", $name);
            $name = str_replace("[", "", $name);
            $name = str_replace("]", "", $name);
            $googleTokenModel->syncLockAccount($emailId, $userId, 1);
            self::syncMailDirectory($storage, $emailId, $name, $accesArray, $imap, $userId, null, 0, 100, 'GMAIL');
            $googleTokenModel->syncLockAccount($emailId, $userId, 0);
            $googleTokenModel->setInitialFetchFlag($accesArray['id']);
        }
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       November 28, 2016
     * @brief      Operations on a single mail
     * @param      $payload   Payload
     */
    public static function singleMailOperation($payload, $callingFunction) {
        $emailAccountSettings = new Email_Account_Settings();
        $accountDetails = $emailAccountSettings->fetchAccountDetailsById($payload['sx_account_id'], $_SESSION['user_id']);
        if ($accountDetails['type'] != 'GMAIL') {
            return false;
        }

        $gmailTokenModel = new Google_Access_Tokens();
        $googletoken = $gmailTokenModel->fetchGoogleAccessTokenById($accountDetails['type_id']);

        $emailId = $googletoken['email_id'];
        $access_token = $googletoken['google_access_token'];
        $client = OAuthManager::getGoogleClient();
        $client->setAccessToken($access_token);

        if ($client->isAccessTokenExpired()) {
            Logger::debug_logger("Google Authentication token expired. Refreshing token.");
            $access_token = OAuthManager::refreshAuthToken($client, $emailId, $_SESSION['user_id']);
        }
        $tokenArray = json_decode($access_token, true);

        $imap = new Imap(getenv('IMAP.ADDRESS.GOOGLE'), getenv('IMAP.PORT.GOOGLE'), 'ssl');

        if (OAuthManager::oauth2Authenticate($imap, null, $emailId, $tokenArray['access_token'])) {
            switch ($callingFunction) {
                case 'emailMarkAsRead':
                    return IMAPCommon::markAsRead($imap, $payload);
                case 'deleteEmail':
                    return IMAPCommon::deleteEmail($imap, $payload);
                case 'fetchInboxUnreadCount':
                    return IMAPCommon::fetchInboxUnreadCount($imap, $payload);
            }
        } else {
            $response['success'] = "false";
            $response['message'] = "Authentication Error";
            return json_encode($response, JSON_NUMERIC_CHECK);
        }
    }

}
