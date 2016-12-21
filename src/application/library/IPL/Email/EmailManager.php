<?php

/**
 * @author     Nikhil N R, <nikhil@salesx.io>
 * @date       August 9, 2016
 * @brief      This class handles all the operations related to Email
 * @details
 */

namespace library\SX\Email;

use \library\SX\Email\SendGoogleEmail as SendGoogleEmail;
use \library\SX\Email\SXMailMessage as SXMailMessage;
use \library\SX\Email\SendEmail as SendEmail;
use \library\SX\Timeline\TimelineManager as TimelineManager;
use \library\SX\Common\Logger as Logger;
use \models\Google_Access_Tokens as Google_Access_Tokens;
use \models\Timeline as Timeline;
use \models\Lead_Contact_Mapping as Lead_Contact_Mapping;
use \models\Lead as Lead;
use \models\Imap_Credentials as Imap_Credentials;
use \models\Email_Settings as Email_Settings;
use \models\Imap_Smtp_Credentials as Imap_Smtp_Credentials;
use \models\Email_Account_Settings as Email_Account_Settings;
use \models\Mailbox as Mailbox;
use \library\SX\Email\Utils as Utils;
use \models\Organization_User_Mapping as Organization_User_Mapping;
use \models\Deal_Contacts_Mapping as Deal_Contacts_Mapping;
use \models\Deal as Deal;
use \models\Contact as Contact;
use \models\Email_Content as Email_Content;

class EmailManager {

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       August 9, 2016
     * @brief      Send Email
     * @param      $payload   Payload data.
     * @return     Json response
     */
    public static function sendEmail($payload) {
        $uniqueId = null;
        $mailUserData = array();
        if (!isset($payload['system_mail'])) {
            $uniqueId = uniqid();
            TimelineManager::addEmailToTimeline($payload, $uniqueId);
        }
        $mailUserData['mail_to'] = $payload['mail_to'];
        $mailUserData['mail_from'] = $payload['mail_from'];
        if (isset($payload['mail_cc'])) {
            $mailUserData['mail_cc'] = $payload['mail_cc'];
        }
        if (isset($payload['system_mail'])) {
            $googleAccessTokenModel = new Google_Access_Tokens();
            $googleToken = $googleAccessTokenModel->fetchGoogleAccessToken($payload['user_id'], $payload['mail_from']['mail_id']);
            $googleAT = $googleToken['google_access_token'];
            if ($payload['system_mail']['template'] == 'invitation_mail') {
                $payload['mail_subject'] = $payload['system_mail']['subject'];
                $zendView = new \Zend_View();
                foreach ($payload['system_mail']['parameters'] as $paramKey => $paramValue) {
                    $zendView->assign($paramKey, $paramValue);
                }
                $zendView->setScriptPath(getenv('APPLICATION.PATH') . '/application/views/scripts/emails/');
                $payload['mail_htmlcontent'] = $zendView->render($payload['system_mail']['template'] . '.phtml');
            }
            $response['success'] = "true";
            $response['message'] = "Mail sent successfully.";
            return json_encode($response, JSON_NUMERIC_CHECK);
        }
        $emailAccountSettingsModel = new Email_Account_Settings();
        $accountDetails = $emailAccountSettingsModel->fetchAccountDetailsById($payload['sx_account_id'], $_SESSION["user_id"]);

        if ($accountDetails['type'] == 'GMAIL') {
            $googleAccessTokenModel = new Google_Access_Tokens();
            $googleToken = $googleAccessTokenModel->fetchGoogleAccessTokenById($accountDetails['type_id']);
            self::sendGoogleEmail($payload, $googleToken, $uniqueId, $accountDetails['type_id'], $mailUserData);
        } else {
            $imapCredModel = new Imap_Smtp_Credentials();
            $imapData = $imapCredModel->fetchImapDataById($accountDetails['type_id']);
            self::sendSMTPmail($payload, $imapData, $uniqueId, $payload['sx_account_id'], $mailUserData);
        }

        $response['success'] = "true";
        $response['message'] = "Mail sent successfully.";
        return json_encode($response, JSON_NUMERIC_CHECK);
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       August 9, 2016
     * @brief      Send email though google.
     * @param      $payload   Payload data.
     * @return     Json response
     */
    public static function sendGoogleEmail($payload, $googleToken, $uniqueId, $sxAccountId, $mailUserData) {
        $mailMessage = self::createMailMessage($payload);
        try {
            SendGoogleEmail::sendEmail($mailMessage, $payload['user_id'], $googleToken, $uniqueId, $sxAccountId, $mailUserData);
        } catch (\Exception $e) {
            Logger::error_logger($e->getMessage());
            Logger::error_logger($e->getTrace());
        }
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       August 29, 2016
     * @brief      Sets primary mail id.
     * @param      $payload   Payload data.
     * @return     Json response
     */
    public static function setPrimaryMailid($payload) {
        if ($payload['type'] == "GMAIL") {
            $gatModel = new Google_Access_Tokens();
            $gatModel->setPrimaryMailid($payload);
        } else {
            $imapCredentialsModel = new Imap_Credentials();
            $imapCredentialsModel->setPrimaryMailid($payload);
        }
        $response['success'] = "true";
        $response['message'] = "Primary mail id updated successfully.";
        return json_encode($response, JSON_NUMERIC_CHECK);
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       September 22, 2016
     * @brief      Add email bcc
     * @param      $payload   Payload data.
     * @return     Json response
     */
    public static function addEmailSettings($payload) {
        if (isset($payload['email_bcc'])) {
            $data['email_bcc'] = json_encode($payload['email_bcc']);
        }
        if (isset($payload['email_signature'])) {
            $data['email_signature'] = $payload['email_signature'];
        }
        $data['user_id'] = $_SESSION["user_id"];

        $emailSettingsModel = new Email_Settings();
        $emailSettingsModel->addEmailSettings($data);

        $response['success'] = "true";
        $response['message'] = "Email settings added successfully.";
        return json_encode($response, JSON_NUMERIC_CHECK);
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       September 26, 2016
     * @edited     Ajith E R, <ajith@salesx.io>
     * @editdate   October 19, 2016
     * @brief      Add Account details
     * @param      $payload   Payload data.
     * @return     Json response
     */
    public static function addSmtpImapAccount($payload) {
        if (Utils::mailIdExists($_SESSION["user_id"], $payload['user_name'])) {
            $response['success'] = "false";
            $response['message'] = "Mail Id already exists.";
            return json_encode($response, JSON_NUMERIC_CHECK);
        }
        if (!Utils::checkImapCredentials($payload)) {
            $response['success'] = "false";
            $response['message'] = "Incorrect IMAP credentials.";
            return json_encode($response, JSON_NUMERIC_CHECK);
        }        
        if (!Utils::checkSMTPCredentials($payload)) {
            $response['success'] = "false";
            $response['message'] = "Incorrect SMTP credentials.";
            return json_encode($response, JSON_NUMERIC_CHECK);
        }
        $smtpImapData['user_id'] = $_SESSION["user_id"];
        $smtpImapData['user_name'] = $payload['user_name'];
        $smtpImapData['password'] = Utils::passwordEncrypt($payload['password']);
        $smtpImapData['imap_port'] = $payload['imap_port'];
        $smtpImapData['imap_host'] = $payload['imap_host'];
        $smtpImapData['imap_encryption'] = $payload['imap_encryption'];
        $smtpImapData['smtp_port'] = $payload['smtp_port'];
        $smtpImapData['smtp_host'] = $payload['smtp_host'];
        $smtpImapData['smtp_encryption'] = $payload['smtp_encryption'];
        $imapSmtpCredential = new Imap_Smtp_Credentials();
        $typeId = $imapSmtpCredential->addSmtpImapCredentials($smtpImapData);

        $accountData['user_id'] = $_SESSION["user_id"];
        $accountData['type'] = 'OTHER';
        $accountData['type_id'] = $typeId;
        $emailAccountSetting = new Email_Account_Settings();
        $accountId = $emailAccountSetting->addAccount($accountData);

        self::initialMailSync();
        
        $accountDetails['sx_account_id'] = $accountId;
        $accountDetails['email_id'] = $payload['user_name'];
        $accountDetails['type'] = 'OTHER';
        $accountDetails['type_id'] = $typeId;
        $accountDetails['imap_port'] = $payload['imap_port'];
        $accountDetails['imap_host'] = $payload['imap_host'];
        $accountDetails['imap_encryption'] = $payload['imap_encryption'];
        $accountDetails['smtp_port'] = $payload['smtp_port'];
        $accountDetails['smtp_host'] = $payload['smtp_host'];
        $accountDetails['smtp_encryption'] = $payload['smtp_encryption'];
        
        $response['success'] = "true";
        $response['message'] = "Email account added successfully.";
        $response['account_details'] = $accountDetails;
        return json_encode($response, JSON_NUMERIC_CHECK);
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       October 19, 2016
     * @brief      Updates the incoming emails of all synched mails ids in the
     *             system.
     * @param      $batchSize   Batch size of each thread.
     * @return     Json response
     */
    public function updateIncomingEmails($batchSize) {

        $pidGmail = pcntl_fork();
        if ($pidGmail == -1) {
            die('could not fork');
        } else if ($pidGmail) {
            //Main thread, do nothing.
        } else {
            $gmailImapHandler = new GmailIMAPHandler();
            $gmailImapHandler->updateIncomingEmail($batchSize);
            return;
        }

        $pidOther = pcntl_fork();
        if ($pidOther == -1) {
            die('could not fork');
        } else if ($pidOther) {
            //Main thread, do nothing.
        } else {
            $imapHandler = new OtherIMAPHandler();
            $imapHandler->updateIncomingEmail($batchSize);
        }
    }

    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       October 25, 2016
     * @brief      Edit SMTP IMAP details.
     * @param      $payload   Payload data.
     * @return     Json response
     */
    public static function editSmtpImapDetails($payload) {
        if (!Utils::checkImapCredentials($payload)) {
            $response['success'] = "false";
            $response['message'] = "Incorrect Credentials.";
            return json_encode($response, JSON_NUMERIC_CHECK);
        }
        $emailAccountModel = new Email_Account_Settings();
        $sxaccountInfo = $emailAccountModel->fetchAccountDetailsById($payload['sx_account_id'], $_SESSION['user_id']);
        $data['id'] = $sxaccountInfo['type_id'];
        $data['user_name'] = $payload['user_name'];
        $data['password'] = Utils::passwordEncrypt($payload['password']);
        $data['imap_port'] = $payload['imap_port'];
        $data['imap_host'] = $payload['imap_host'];
        $data['imap_encryption'] = $payload['imap_encryption'];
        $data['smtp_port'] = $payload['smtp_port'];
        $data['smtp_host'] = $payload['smtp_host'];
        $data['smtp_encryption'] = $payload['smtp_encryption'];
        $imapSmtpCredential = new Imap_Smtp_Credentials();
        $imapSmtpCredential->editData($data);

        $accountDetails['sx_account_id'] = $payload['sx_account_id'];
        $accountDetails['email_id'] = $payload['user_name'];
        $accountDetails['type'] = 'OTHER';
        $accountDetails['type_id'] = $sxaccountInfo['type_id'];
        $accountDetails['imap_port'] = $payload['imap_port'];
        $accountDetails['imap_host'] = $payload['imap_host'];
        $accountDetails['imap_encryption'] = $payload['imap_encryption'];
        $accountDetails['smtp_port'] = $payload['smtp_port'];
        $accountDetails['smtp_host'] = $payload['smtp_host'];
        $accountDetails['smtp_encryption'] = $payload['smtp_encryption'];

        $response['success'] = "true";
        $response['message'] = "Successfully Edited.";
        $response['account_details'] = $accountDetails;
        return json_encode($response, JSON_NUMERIC_CHECK);
    }

    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       October 25, 2016
     * @brief      Delete SMTP IMAP account.
     * @param      $payload   Payload data.
     * @return     Json response
     */
    public static function deleteSmtpImapAccount($payload) {
        $data = $payload['account_id'];
        $imapSmtpCredential = new Imap_Smtp_Credentials();
        $imapSmtpCredential->deleteSmtpImapAccount($data);
        $response['success'] = "true";
        $response['message'] = "Account Successfully Deleted.";
        return json_encode($response, JSON_NUMERIC_CHECK);
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       October 26, 2016
     * @brief      Initiates the initial mail sync operation.
     * @return     Json response
     */
    public static function initialMailSync() {
        exec('php ' . getenv('APPLICATION.PATH') . '/scripts/initialMailSync.php ' . escapeshellarg($_SESSION['user_id']) . ' > /dev/null &');
        $response['success'] = "true";
        $response['message'] = "Sync operation initiated.";
        return json_encode($response, JSON_NUMERIC_CHECK);
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       October 26, 2016
     * @brief      Initiates the initial mail sync operation.
     */
    public static function fetchInitialUserMail($userId) {
        $emailAccountSettings = new Email_Account_Settings();
        $accountDetails = $emailAccountSettings->fetchAccountDetailsByUserId($userId);
        foreach ($accountDetails as $accountDetail) {
            if ($accountDetail['type'] == 'GMAIL') {
                $googleAccessTokenModel = new Google_Access_Tokens();
                $gmailId = $googleAccessTokenModel->fetchMailDetails($accountDetail['type_id']);
                if ($gmailId != null) {
                    $gmailId['sx_account_id'] = $accountDetail['id'];
                    try {
                        GmailIMAPHandler::initialMailSync($userId, $gmailId);
                    } catch (\Exception $ex) {
                        //Rollback unlock
                        Logger::error_logger($ex->getMessage());
                        Logger::error_logger($ex->getLine());
                        Logger::error_logger($ex->getFile());
                    }
                }
            }
            if ($accountDetail['type'] == 'OTHER') {
                $imapCredModel = new Imap_Smtp_Credentials();
                $imapData = $imapCredModel->fetchImapDataById($accountDetail['type_id'], true);
                if ($imapData != null) {
                    $imapData['sx_account_id'] = $accountDetail['id'];
                    try {
                        OtherIMAPHandler::initialMailSync($userId, $imapData);
                    } catch (\Exception $ex) {
                        //Rollback unlock
                        Logger::error_logger($ex->getMessage());
                        Logger::error_logger($ex->getLine());
                        Logger::error_logger($ex->getFile());
                    }
                }
            }
        }
    }

    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       October 28, 2016
     * @brief      User sync mail fetch using skip and limit.
     */
    public static function fetchUserMail($payload) {
        $skip = 0;
        $limit = 20;
        if (isset($payload['skip']) && !empty($payload['skip'])) {
            $skip = $payload['skip'];
        }
        if (isset($payload['limit']) && !empty($payload['limit'])) {
            $limit = $payload['limit'];
        }
        if (isset($payload['sx_account_id'])) {
            $sxaccountid = $payload['sx_account_id'];
        } else {
            $emailAccountSettings = new Email_Account_Settings();
            $accountDetails = $emailAccountSettings->fetchAccountDetailsByUserId($_SESSION['user_id']);
            $accountsxID = array();
            foreach ($accountDetails as $accountDetail) {
                array_push($accountsxID, $accountDetail['id']);
            }
            $sxaccountid = $accountsxID;
        }
        foreach ($sxaccountid as $id) {
            $synCompleteSxAccount = array();
            $synNotCompleteSxAccount = array();
            $emailSettingModel = new Email_Account_Settings();
            $accountType = $emailSettingModel->fetchAccountDetailsById($id, $_SESSION['user_id']);
            $type_id = $accountType['type_id'];
            if ($accountType['type'] == "OTHER") {
                $imapsmtpModel = new Imap_Smtp_Credentials();
                $imapsmtpSync = $imapsmtpModel->fetchImapDataById($type_id);
                if ($imapsmtpSync['initial_fetch'] == 1) {
                    if (isset($payload['type']) && $payload['type'] == 'INBOX') {
                        $type['type'] = 'Inbox';
                    } else if (isset($payload['type']) && $payload['type'] == 'SENT') {
                        $type['type'] = '';
                    } else if (isset($payload['type']) && $payload['type'] == 'DRAFT') {
                        $type['type'] = '';
                    } else {
                        $type['type'] = "Inbox";
                    }
                    $type['id'] = $id;
                    array_push($synCompleteSxAccount, $type);
                } else {
                    array_push($synNotCompleteSxAccount, $id);
                }
            }
            if ($accountType['type'] == "GMAIL") {
                $googleMail = new Google_Access_Tokens();
                $fetchSync = $googleMail->fetchAllDetailsById($type_id);
                if ($fetchSync['initial_fetch'] == 1) {
                    if (isset($payload['type']) && $payload['type'] == 'INBOX') {
                        $type['type'] = 'INBOX';
                    } else if (isset($payload['type']) && $payload['type'] == 'SENT') {
                        $type['type'] = 'GmailSent Mail';
                    } else if (isset($payload['type']) && $payload['type'] == 'DRAFT') {
                        $type['type'] = 'GmailDrafts';
                    } else {
                        $type['type'] = "INBOX";
                    }
                    $type['id'] = $id;
                    array_push($synCompleteSxAccount, $type);
                } else {
                    array_push($synNotCompleteSxAccount, $id);
                }
            }
        }
        if (($synCompleteSxAccount == null) && ($synNotCompleteSxAccount != null)) {
            $response['success'] = "false";
            $response['mesage'] = "Mail fetch progressing.. Please wait a moment";
            return json_encode($response, JSON_NUMERIC_CHECK);
        }
        $userMails = array();
        foreach ($synCompleteSxAccount as $id) {
            $mailboxModel = new Mailbox();
            $user_id = (integer) $_SESSION['user_id'];
            $result = $mailboxModel->getMailForUser($user_id, $id['id'], $skip, $limit, $id['type']);
            $MailList = json_decode(json_encode(iterator_to_array($result)), TRUE);
            foreach ($MailList as $mail) {
                $fetchMailData = self::fetchMailLeadContactDetails($mail);
                if ($fetchMailData != null) {
                    array_push($userMails, $fetchMailData);
                }
            }
        }
        if ($userMails == null) {
            $response['success'] = "false";
            $response['mesage'] = "Ooops No Mails Found";
            return json_encode($response, JSON_NUMERIC_CHECK);
        }
        $directory['userid'] = $_SESSION['user_id'];
        $directory['type'] = $type;
        $mailboxModel = new Mailbox();
        $directorySize = $mailboxModel->fetchMailboxDirectorySize($directory);
        $fetchSize = sizeof($userMails);
        if ($directorySize == $fetchSize + $skip) {
            $response['is_mail_finish'] = 1;
        } else {
            $response['is_mail_finish'] = 0;
        }
        $response['success'] = "true";
        $response['email_box'] = $userMails;
        return json_encode($response, JSON_NUMERIC_CHECK);
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       November 3, 2016
     * @brief      Send email though SMTP.
     * @param      $payload   Payload data.
     * @return     Json response
     */
    public static function sendSMTPmail($payload, $imapData, $uniqueId, $sxAccountId, $mailUserData) {
        $mailMessage = self::createMailMessage($payload);
        try {
            SendGoogleEmail::sendEmail($mailMessage, $payload['user_id'], null, $uniqueId, $sxAccountId, $mailUserData, $imapData);
        } catch (\Exception $e) {
            Logger::error_logger($e->getMessage());
            Logger::error_logger($e->getTrace());
        }
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       November 3, 2016
     * @brief      Creates a mail message object.
     * @param      $payload   Payload data.
     * @return     Json response
     */
    public static function createMailMessage($payload) {
        $mailMessage = new SXMailMessage();
        $mailMessage->setFrom($payload['mail_from']['mail_id']);
        $mailMessage->setFromName($payload['mail_from']['name']);

        $mailIds = array();
        foreach ($payload['mail_to'] as $toArray) {
            array_push($mailIds, $toArray['mail_id']);
        }

        $mailMessage->setTo($mailIds);
        $mailIds = array();
        if (isset($payload['mail_cc'])) {
            foreach ($payload['mail_cc'] as $toArray) {
                array_push($mailIds, $toArray['mail_id']);
            }
            $mailMessage->setCC($mailIds);
        }

        $mailIds = array();
        if (isset($payload['mail_bcc'])) {
            foreach ($payload['mail_bcc'] as $toArray) {
                array_push($mailIds, $toArray['mail_id']);
            }
            $mailMessage->setBCC($mailIds);
        }
        if (isset($payload['mail_subject'])) {
            $mailMessage->setSubject($payload['mail_subject']);
        }
        $mailMessage->setIsHTML(true);
        if (isset($payload['mail_htmlcontent'])) {
            $mailMessage->setBody($payload['mail_htmlcontent']);
        }
        if (isset($payload['attachments'])) {
            $mailMessage->setAttachment($payload['attachments']);
        }

        if (isset($payload['mail_settings'])) {
            $mailMessage->setMailSettings($payload['mail_settings']);
        }

        if (isset($payload['references'])) {
            $mailMessage->setReferences($payload['references']);
        }
        return $mailMessage;
    }

    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       November 14, 2016
     * @brief      Load email Attachments.
     * @param      $payload   Payload data.
     * @return     Json response
     */
    public static function loadAttachment($data) {
        $attachmentPath = $data['attachment_path'];
        $path = str_replace("/var/www/SalesXApi/src/application/files/emailattachments/", "", $attachmentPath);
        $file = getenv('APPLICATION.PATH') . '/application/files/emailattachments/' . $path;
        $filesize = filesize($file);
        $pathinfo = pathinfo($file);
        if(file_get_contents($file) == null){
        $response['success'] = 'false';
        $response['message'] = 'file not found';         
        return json_encode($response, JSON_NUMERIC_CHECK); 
        }
        $response['success'] = 'true';
        $response['type'] = mime_content_type($file);
        $response['filename'] = $pathinfo['basename'];
        $response['data'] = base64_encode(file_get_contents($file));         
        return json_encode($response, JSON_NUMERIC_CHECK);        
    }

    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       November 22, 2016
     * @brief      Fetch singlemail details.
     * @param      $payload   Payload data.
     * @return     Json response
     */
    public static function SingleMailDetail($payload) {
        $mailboxModel = new Mailbox();
        $mail = $mailboxModel->getSingleMailDetails($payload['sxuid']);
        if ($mail == null) {
            $response['success'] = "false";
            $response['mesage'] = "Ooops No Mails Found";
            return json_encode($response, JSON_NUMERIC_CHECK);
        }
        $flag = $mail['flags'];
        if (!isset($flag['Seen'])) {
            $emailRead['sxuid'] = $payload['sxuid'];
            $emailRead['sx_account_id'] = $mail['sx_account_id'];
            $emailRead['read'] = 1;
            $emailRead['m_id'] = $mail['m_id'];
            $emailRead['type'] = $mail['type'];
            self::emailMarkAsRead($emailRead);
            $mail = $mailboxModel->getSingleMailDetails($payload['sxuid']);
        }
        $fetchMailData = self::fetchMailLeadContactDetails($mail);

        $response['success'] = "true";
        $response['mail_details'] = $fetchMailData;
        return json_encode($response, JSON_NUMERIC_CHECK);
    }

    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       November 30, 2016
     * @brief      Fetch mail lead contact details.
     * @param      $mail mail info.
     * @return     details.
     */
    public static function fetchMailLeadContactDetails($mail) {
        $fetchMailData['m_id'] = $mail['m_id'];
        $fetchMailData['sx_account_id'] = $mail['sx_account_id'];
        $fetchMailData['is_mail_read'] = 0;
        $flag = $mail['flags'];
        if (isset($flag['Seen'])) {
            $fetchMailData['is_mail_read'] = 1;
        }
        $fetchMailData['is_mail_open'] = 0;
        if (isset($mail['mail_open_count'])) {
            $fetchMailData['is_mail_open'] = $mail['mail_open_count'];
        }
        $fetchMailData['mail_opened_details'] = array();
        if (isset($mail['mail_opened_details'])) {
            $fetchMailData['mail_opened_details'] = $mail['mail_opened_details'];
        }
        $fetchMailData['is_mail_flagged'] = 0;
        if (isset($mail['is_mail_flagged'])) {
            $fetchMailData['is_mail_flagged'] = $mail['is_mail_flagged'];
        }
        $fetchMailData['reference_ids'] = "";
        $headers = $mail['headers'];
        if (isset($headers['References'])) {
            $fetchMailData['reference_ids'] = $headers['References'];
        }
        $fetchMailData['replay_to'] = "";
        if (isset($mail['In-Reply-To'])) {
            $fetchMailData['replay_to'] = $mail['In-Reply-To'];
        }
        $fetchMailData['is_mail_replied'] = 0;
        $fetchMailData['topLines'] = $mail['topLines'];
        $fetchMailData['user_id'] = $mail['user_id'];
        if (($mail['directory'] == 'INBOX') || ($mail['directory'] == 'Inbox')) {
            $fetchMailData['directory'] = "INBOX";
        }
        if (($mail['directory'] == 'GmailSent Mail') || ($mail['directory'] == 'Sent')) {
            $fetchMailData['directory'] = "SENT";
        }
        if ($mail['directory'] == 'GmailDrafts') {
            $fetchMailData['directory'] = "DRAFT";
        }
        $fetchMailData['type'] = $mail['type'];
        $fetchMailData['date'] = $mail['date'];
        $fetchMailData['subject'] = $mail['subject'];
        $fetchMailData['fromAddress'] = $mail['fromAddress'];
        $fetchMailData['fromName'] = $mail['fromName'];
        $arrayToName = array();
        $toEmailArray = $mail['mail_user_data'];
        $toEmailData = $toEmailArray['mail_to'];
        foreach ($toEmailData as $key => $Emailto) {
            $EMAIL['name'] = $Emailto['name'];
            $EMAIL['id'] = $Emailto['mail_id'];
            array_push($arrayToName, $EMAIL);
        }
        $fetchMailData['toName'] = "[]";
        if ($arrayToName != null) {
            $fetchMailData['toName'] = $arrayToName;
        }
        $fetchMailData['cc'] = $mail['cc'];
        $$fetchMailData['bcc'] = '';
        if (isset($mail['bcc'])) {
            $fetchMailData['bcc'] = $mail['bcc'];
        }
        $fetchMailData['replyTo'] = $mail['replyTo'];
        $fetchMailData['message_id'] = $mail['messageId'];
        $fetchMailData['mail_textcontent'] = $mail['textPlain'];
        $fetchMailData['mail_htmlcontent'] = $mail['textHtml'];
        $fetchMailData['attachments'] = $mail['attachments'];
        $fetchMailData['sxuid'] = $mail['sxuid'];
        $fetchMailData['mail_user_data'] = $mail['mail_user_data'];
        if (isset($mail['mail_settings'])) {
            $fetchMailData['mail_settings'] = $mail['mail_settings'];
        }
        $userdata = $mail['mail_user_data'];
        $dealSearch = null;
        $arrayContacts = array();
        if ($fetchMailData['directory'] == 'INBOX') {
            $from = $userdata['mail_from'];
            foreach ($from as $key => $mailfrom) {
                if (($mailfrom['type'] == 'CONTACT') && ($mailfrom['server_id'] != 0)) {
                    array_push($arrayContacts, $mailfrom['server_id']);
                }
            }
        }
        if ($arrayContacts != null) {
            $dealSearch = self::searchContactDeals($fetchMailData, $arrayContacts);
            if ($dealSearch != null) {
                return $dealSearch;
            }
        } else if (($arrayContacts == null) && ($dealSearch == null)) {
            $to = $userdata['mail_to'];
            foreach ($to as $key => $mailto) {
                if (($mailto['type'] == 'CONTACT') && ($mailto['server_id'] != 0)) {
                    array_push($arrayContacts, $mailto['server_id']);
                }
            }
            if ($arrayContacts != null) {
                $dealSearch = self::searchContactDeals($fetchMailData, $arrayContacts);
                if ($dealSearch != null) {
                    return $dealSearch;
                }
            } if (isset($userdata['mail_cc'])) {
                $cc = $userdata['mail_cc'];
                foreach ($cc as $key => $mailcc) {
                    if (($mailcc['type'] == 'CONTACT') && ($mailcc['server_id'] != 0)) {
                        array_push($arrayContacts, $mailcc['server_id']);
                    }
                }
            }
            if (isset($userdata['mail_bcc'])) {
                $bcc = $userdata['mail_cc'];
                foreach ($bcc as $key => $mailbcc) {
                    if (($mailbcc['type'] == 'CONTACT') && ($mailbcc['server_id'] != 0)) {
                        array_push($arrayContacts, $mailbcc['server_id']);
                    }
                }
            }if ($arrayContacts != null) {
                $dealSearch = self::searchContactDeals($fetchMailData, $arrayContacts);
                if ($dealSearch != null) {
                    return $dealSearch;
                } else {
                    $fetchMailData['deal_id'] = "";
                    $fetchMailData['deal_value'] = "";
                    $fetchMailData['lead_id'] = "";
                    $fetchMailData['lead_name'] = "";
                    $fetchMailData['deal_payment'] = "";
                    $fetchMailData['contact_id'] = "";
                    $fetchMailData['contact_name'] = "";
                    return $fetchMailData;
                }
            } else if ($arrayContacts == null) {
                $fetchMailData['deal_id'] = "";
                $fetchMailData['deal_value'] = "";
                $fetchMailData['lead_id'] = "";
                $fetchMailData['lead_name'] = "";
                $fetchMailData['deal_payment'] = "";
                $fetchMailData['contact_id'] = "";
                $fetchMailData['contact_name'] = "";
                return $fetchMailData;
            }
        }
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       November 28, 2016
     * @brief      Edit //Seen flag
     * @param      $payload   Payload
     */
    public static function emailMarkAsRead($payload) {
        if ($payload['type'] == 'GMAIL') {
            return GmailIMAPHandler::singleMailOperation($payload, 'emailMarkAsRead');
        } elseif ($payload['type'] == 'OTHER') {
            return OtherIMAPHandler::singleMailOperation($payload, 'emailMarkAsRead');
        }
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       November 30, 2016
     * @brief      Delete email
     * @param      $payload   Payload
     */
    public static function deleteEmail($payload) {
        if ($payload['type'] == 'GMAIL') {
            return GmailIMAPHandler::singleMailOperation($payload, 'deleteEmail');
        } elseif ($payload['type'] == 'OTHER') {
            return OtherIMAPHandler::singleMailOperation($payload, 'deleteEmail');
        }
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       November 30, 2016
     * @brief      Fetch mailbox size
     * @param      $payload   Payload
     */
    public static function fetchMailboxSize($userId) {
        $mailBox = New Mailbox();
        $mailBoxSize = $mailBox->fetchMailboxSize($userId);
        echo print_r($mailBoxSize, true);
    }

    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       December 1, 2016
     * @brief      Set internal flag to mail
     * @param      $payload   Payload
     */
    public static function setEmailFlag($payload) {
        $sxuid = $payload['sxuid'];
        $flag = $payload['email_flag'];
        $emailContent = new Email_Content('Mailbox');
        $emailContent->updateInternalMailFlag($sxuid, $flag);

        $response['success'] = "true";
        $response['message'] = "Email flag added.";
        return json_encode($response, JSON_NUMERIC_CHECK);
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       December 1, 2016
     * @brief      Schedule mail
     * @param      $payload   Payload
     */
    public static function scheduleMail($payload) {
        $emailModel = new Email_Content('Mail_Scheduled');
        $content['payload'] = $payload;
        $content['user_id'] = $_SESSION["user_id"];
        $content['date'] = $payload['mail_settings']['mail_scheduled_date'];
        $emailModel->saveEmail($content);
        $response['success'] = "true";
        $response['message'] = "Mail scheduled successfully.";
        return json_encode($response, JSON_NUMERIC_CHECK);
    }

    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       December 2, 2016
     * @brief      Contact lead Deal Value fetch.
     * @param      $data
     */
    public static function searchContactDeals($fetchMailData, $arrayContacts) {        
        $contactIds = array_unique($arrayContacts, SORT_REGULAR);        
        $arrayleadID = array();
        foreach ($contactIds as $ids) {
            $contactModel = new Contact();
            $leadId = $contactModel->fetchContact($ids);
            if($leadId != ""){
            $details['lead_id'] = $leadId['lead_id'];
            $details['contact_id'] = $ids;
            $details['contact_name'] = $leadId['name'];
            array_push($arrayleadID, $details);
            }
        }        
        if ($arrayleadID == null) {
                $fetchMailData['deal_id'] = "";
                $fetchMailData['deal_value'] = "";
                $fetchMailData['lead_id'] = "";
                $fetchMailData['lead_name'] = "";
                $fetchMailData['deal_payment'] = "";
                $fetchMailData['contact_id'] = "";
                $fetchMailData['contact_name'] = "";                
                return $fetchMailData; 
        }        
        if ($arrayleadID != null) {
            $leadid = array_unique($arrayleadID, SORT_REGULAR);
            $arrayDeal = array();
            foreach ($leadid as $key => $lid) {
                $deal = new Deal();
                $dealCompleteDetails = $deal->fetchDealForLead($lid['lead_id']);
                foreach ($dealCompleteDetails as $Deal) {
                    $dealDetails['id'] = $Deal['id'];
                    $dealDetails['value'] = $Deal['value'];
                    $dealDetails['payment_type'] = $Deal['payment_type'];
                    $dealDetails['status'] = $Deal['status'];
                    $dealDetails['lead_id'] = $lid['lead_id'];
                    $dealDetails['contact_id'] = $contactIds;
                    $dealDetails['contact_name'] = $lid['contact_name'];
                    array_push($arrayDeal, $dealDetails);
                }
            }
        }        
        if ($arrayDeal == null) {
            return null;
        }        
        foreach ($arrayDeal as $key => $row) {
            $volume[$key] = $row['value'];
        }
        array_multisort($volume, SORT_DESC, $arrayDeal);
        $leadModel = new Lead();
        $leadDetails = $leadModel->fetchLead($arrayDeal[0]['lead_id']);
        $fetchMailData['deal_id'] = $arrayDeal[0]['id'];
        $fetchMailData['deal_value'] = $arrayDeal[0]['value'];
        $fetchMailData['deal_payment'] = $arrayDeal[0]['payment_type'];
        $fetchMailData['lead_id'] = $arrayDeal[0]['lead_id'];
        $fetchMailData['lead_name'] = $leadDetails['name'];
        $fetchMailData['contact_id'] = $arrayDeal[0]['contact_id'];
        $fetchMailData['contact_name'] = $arrayDeal[0]['contact_name'];
        return $fetchMailData;
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       December 1, 2016
     * @brief      Send scheduled mails
     * @param      $payload   Payload
     */
    public function sendScheduledMail() {
        $emailModel = new Email_Content('Mail_Scheduled');
        $scheduledArray = $emailModel->fetchScheduledMail();
        foreach ($scheduledArray as $scheduled) {
            $_SESSION["user_id"] = $scheduled->user_id;
            $payload = json_decode(json_encode($scheduled->payload), true);
            $response = self::sendEmail($payload);
            echo print_r($response, true);
            $response = json_decode($response, true);
            if (isset($response['success']) && $response['success'] == "true") {
                $emailModel->deleteScheduledMail($scheduled->_id);
            }
        }
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       December 2, 2016
     * @brief      Fetch inbox unread mail count
     * @param      $payload   Payload
     */
    public static function fetchInboxUnreadCount($payload) {

        $emailAccounts = new Email_Account_Settings();
        $accountDetails = $emailAccounts->fetchAccountDetailsById($payload['sx_account_id'], $_SESSION['user_id']);

        if ($accountDetails['type'] == 'GMAIL') {
            return GmailIMAPHandler::singleMailOperation($payload, 'fetchInboxUnreadCount');
        } else {
            return OtherIMAPHandler::singleMailOperation($payload, 'fetchInboxUnreadCount');
        }
    }

    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       December 7, 2016
     * @brief      Delete SX Sync Mail Accoutns.
     * @param      $data
     */
    public static function deleteSxSyncAccount($payload) {
        $emailSettingModel = new Email_Account_Settings();
        $mailSettings = $emailSettingModel->fetchAccountDetailsById($payload['sx_account_id'], $_SESSION['user_id']);
        if ($mailSettings == null) {
            $response['success'] = "false";
            $response['message'] = "Account not found.";
            return json_encode($response, JSON_NUMERIC_CHECK);
        }
        if ($mailSettings['type'] == 'OTHER') {
            $imapSmtpCredential = new Imap_Smtp_Credentials();
            $imapSmtpCredential->deleteSmtpImapAccount($mailSettings['type_id']);
            $accountData['userId'] = $_SESSION['user_id'];
            $accountData['typeId'] = $mailSettings['type_id'];
            $emailSettingModel = new Email_Account_Settings();
            $emailSettingModel->deleteAccount($accountData);
            $mailboxModel = new Mailbox();
            $mailboxModel->deleteAccountMails($payload['sx_account_id'], $_SESSION['user_id']);
            $response['success'] = "true";
            $response['message'] = "Account Successfully Deleted.";
            return json_encode($response, JSON_NUMERIC_CHECK);
        }
        if ($mailSettings['type'] == 'GMAIL') {
            $googleAccessModel = new Google_Access_Tokens();
            $googleAccessModel->deleteGoogleAccount($mailSettings['type_id']);
            $accountData['userId'] = $_SESSION['user_id'];
            $accountData['typeId'] = $mailSettings['type_id'];
            $emailSettingModel = new Email_Account_Settings();
            $emailSettingModel->deleteAccount($accountData);
            $mailboxModel = new Mailbox();
            $mailboxModel->deleteAccountMails($payload['sx_account_id'], $_SESSION['user_id']);
            $response['success'] = "true";
            $response['message'] = "Account Successfully Deleted.";
            return json_encode($response, JSON_NUMERIC_CHECK);
        }
    }

}
