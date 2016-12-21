<?php

/**
 * @author     Nikhil N R, <nikhil@salesx.io>
 * @date       April 30, 2016
 * @brief      This class is used receiving emails
 */

namespace library\SX\Email;

use \library\SX\Email\Utils as EmailUtils;
use \library\SX\Common\Logger as Logger;
use \library\SX\Common\AsynchronousOperations as AsynchronousOperations;
use \models\Imap_Smtp_Credentials as Imap_Smtp_Credentials;
use \models\Email_Account_Settings as Email_Account_Settings;
use \PhpImap\Mailbox as Mailbox;
use \Zend\Mail\Protocol\Imap as Imap;
use \Zend\Mail\Storage\Imap as Storage;

class OtherIMAPHandler {

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       May 21, 2016
     * @brief      Fetches all mail boxes.
     */
    public static function syncAllMail($dataChunk) {
        try {
            $imap = new Imap();
            $imap->connect($dataChunk->imap_host, $dataChunk->imap_port, 'ssl');
            $imap->login($dataChunk->user_name, trim(EmailUtils::passwordDecrypt($dataChunk->password)));
            $storage = new Storage($imap);
            $imapModel = new Imap_Smtp_Credentials();
            $sxIdArray['id'] = $dataChunk->id;
            $emailAccountSettingsModel = new Email_Account_Settings();
            $sxIdArray['sx_account_id'] = $emailAccountSettingsModel->fetchAccountDetails('OTHER', $dataChunk->id, true);

            try {
                $storage->selectFolder('Inbox');
            } catch (\Exception $e) {
                Logger::error_logger($e->getMessage());
                return;
            }
            $imapModel->syncLockAccount($dataChunk->user_name, $dataChunk->user_id, 1);
            GmailIMAPHandler::syncMailDirectory($storage, $dataChunk->user_name, 'Inbox', $sxIdArray, $imap, $dataChunk->user_id, null, null, null, 'OTHER');
            $imapModel->syncLockAccount($dataChunk->user_name, $dataChunk->user_id, 0);
            try {
                $storage->selectFolder('Sent');
            } catch (\Exception $e) {
                Logger::error_logger($e->getMessage());
                return;
            }
            $imapModel->syncLockAccount($dataChunk->user_name, $dataChunk->user_id, 1);
            GmailIMAPHandler::syncMailDirectory($storage, $dataChunk->user_name, 'Sent', $sxIdArray, $imap, $dataChunk->user_id, null, null, null, 'OTHER');
            $imapModel->syncLockAccount($dataChunk->user_name, $dataChunk->user_id, 0);
            try {
                $storage->selectFolder('Draft');
                $draftName = 'Draft';
            } catch (\Exception $e) {
                Logger::error_logger($e->getMessage());
                try {
                    $storage->selectFolder('Drafts');
                    $draftName = 'Drafts';
                } catch (\Exception $e) {
                    Logger::error_logger($e->getMessage());
                    return;
                }
            }
            $imapModel->syncLockAccount($dataChunk->user_name, $dataChunk->user_id, 1);
            GmailIMAPHandler::syncMailDirectory($storage, $dataChunk->user_name, $draftName, $sxIdArray, $imap, $dataChunk->user_id, null, null, null, 'OTHER');
            $imapModel->syncLockAccount($dataChunk->user_name, $dataChunk->user_id, 0);
        } catch (\Exception $e) {
            Logger::debug_logger("EXCEPTION");
            Logger::debug_logger($e);
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
        $imapCredentials = new Imap_Smtp_Credentials();
        $credentials = $imapCredentials->fetchAllData();
        $batchData = EmailUtils::generateBatchData($credentials, $batchSize);
        AsynchronousOperations::fetchIncomingImapMail($batchData);
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       October 26, 2016
     * @brief      Initial Mail Sync operation
     * @param      $userId   User Id
     */
    public static function initialMailSync($userId, $credentials) {
        try {
            $imap = new Imap();
            $imap->connect($credentials['imap_host'], $credentials['imap_port'], 'ssl');
            $imap->login($credentials['user_name'], trim(EmailUtils::passwordDecrypt($credentials['password'])));
            $storage = new Storage($imap);
            $imapModel = new Imap_Smtp_Credentials();
            $sxIdArray['id'] = $credentials['id'];
            $sxIdArray['sx_account_id'] = $credentials['sx_account_id'];

            try {
                $storage->selectFolder('Inbox');
            } catch (\Exception $e) {
                Logger::error_logger($e->getMessage());
                return;
            }
            $imapModel->syncLockAccount($credentials['user_name'], $userId, 1);
            GmailIMAPHandler::syncMailDirectory($storage, $credentials['user_name'], 'Inbox', $sxIdArray, $imap, $userId, null, 0, 500, 'OTHER');
            $imapModel->syncLockAccount($credentials['user_name'], $userId, 0);
            try {
                $storage->selectFolder('Sent');
            } catch (\Exception $e) {
                Logger::error_logger($e->getMessage());
                return;
            }
            $imapModel->syncLockAccount($credentials['user_name'], $userId, 1);
            GmailIMAPHandler::syncMailDirectory($storage, $credentials['user_name'], 'Sent', $sxIdArray, $imap, $userId, null, 0, 100, 'OTHER');
            $imapModel->syncLockAccount($credentials['user_name'], $userId, 0);
            $imapModel->setInitialFetchFlag($credentials['id']);

            try {
                $storage->selectFolder('Draft');
                $draftName = 'Draft';
            } catch (\Exception $e) {
                Logger::error_logger($e->getMessage());
                try {
                    $storage->selectFolder('Drafts');
                    $draftName = 'Drafts';
                } catch (\Exception $e) {
                    Logger::error_logger($e->getMessage());
                    return;
                }
            }
            $imapModel->syncLockAccount($credentials['user_name'], $userId, 1);
            GmailIMAPHandler::syncMailDirectory($storage, $credentials['user_name'], $draftName, $sxIdArray, $imap, $userId, null, 0, 100, 'OTHER');
            $imapModel->syncLockAccount($credentials['user_name'], $userId, 0);
            $imapModel->setInitialFetchFlag($credentials['id']);
        } catch (\Exception $e) {
            Logger::debug_logger("EXCEPTION");
            Logger::debug_logger($e);
        }
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       October 24, 2016
     * @brief      Updates the sent mail
     * @param      $uniqueId   Unique Id.
     */
    public static function updateSentMail($arguments) {
        try {
            $imap = new Imap();
            if (strlen($arguments['imap_data']['imap_encryption']) == 0) {
                $arguments['imap_data']['imap_encryption'] = false;
            }
            $imap->connect($arguments['imap_data']['imap_host'], $arguments['imap_data']['imap_port'], $arguments['imap_data']['imap_encryption']);
            $imap->login($arguments['imap_data']['user_name'], $arguments['imap_data']['password']);
            $storage = new Storage($imap, 'Sent');
            $searchParams['sentmail_uid'] = $arguments['unique_id'];
            $sxIdArray['id'] = $arguments['imap_data']['id'];
            $sxIdArray['sx_account_id'] = $arguments['sx_account_id'];
            $mailUserData = $arguments['mail_user_data'];
            if (isset($arguments['mail_reminder'])) {
                $mailReminder = $arguments['mail_reminder_date'];
            } else {
                $mailReminder = -1;
            }
            GmailIMAPHandler::syncMailDirectory($storage, $arguments['imap_data']['user_name'], 'Sent', $sxIdArray, $imap, $arguments['user_id'], $searchParams, null, null, 'OTHER', $mailUserData, $mailReminder);
        } catch (\Exception $e) {
            Logger::debug_logger("EXCEPTION");
            Logger::debug_logger($e->getMessage());
            Logger::error_logger($e->getTrace());
        }
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       November 28, 2016
     * @brief      Edit //Seen flag gor Other mail
     * @param      $payload   Payload
     */
    public static function singleMailOperation($payload, $callingFunction) {
        $emailAccountSettings = new Email_Account_Settings();
        $accountDetails = $emailAccountSettings->fetchAccountDetailsById($payload['sx_account_id'], $_SESSION['user_id']);
        if ($accountDetails['type'] != 'OTHER') {
            return false;
        }
        $smtpImapModel = new Imap_Smtp_Credentials();
        $credentials = $smtpImapModel->fetchImapDataById($accountDetails['type_id'], false);

        $imap = new Imap();
        try {
            $imap->connect($credentials['imap_host'], $credentials['imap_port'], 'ssl');
            $imap->login($credentials['user_name'], trim(EmailUtils::passwordDecrypt($credentials['password'])));
            switch ($callingFunction) {
                case 'emailMarkAsRead':
                    return IMAPCommon::markAsRead($imap, $payload);
                case 'deleteEmail':
                    return IMAPCommon::deleteEmail($imap, $payload);
                case 'fetchInboxUnreadCount':
                    return IMAPCommon::fetchInboxUnreadCount($imap, $payload);
            }
        } catch (\Exception $ex) {
            $response['success'] = "false";
            $response['message'] = "Authentication Error";
            return json_encode($response, JSON_NUMERIC_CHECK);
        }
    }

}
