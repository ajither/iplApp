<?php

/**
 * @author     Nikhil N R, <nikhil@salesx.io>
 * @date       May 17, 2016
 * @brief      This class is used for utility functions for Email
 */

namespace library\SX\Email;

use \Zend\Mail\Storage\Message as Message;
use \Zend\Mail\Protocol\Imap as Imap;
use \Zend\Mail\Protocol\Smtp\Auth\Login as Login;
use \library\SX\Common\Logger as Logger;
use \library\SX\Common\Constants as Constants;
use \models\Email_Content as Email_Content;
use \models\Imap_Smtp_Credentials as Imap_Smtp_Credentials;
use \models\Contact as Contact;
use \models\User_Profile as User_Profile;
use \models\Email_Reminders as Email_Reminders;
use \models\Mailbox as Mailbox;

class Utils {

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       May 23, 2016
     * @brief      Generates an array representaion of the mail from google.
     * @param      $mail Message
     * @param      $directory  Directory name
     * @param      $attachmentDir  Attachment directory name
     * @param      $mId  Mail identification number
     * @param      $userId  User Id
     * @return     Mail Array
     */
    public static function generateMailArray(Message $mail, $directory, $attachmentDir, $mId, $userId, $type) {
        $emailArray = array();
        $emailArray['flags'] = Utils::normalizeFlagKeys($mail->getFlags());

        $emailArray['topLines'] = $mail->getTopLines();

        $emailArray['m_id'] = (Integer) $mId;
        $emailArray['user_id'] = (Integer) $userId;
        $emailArray['directory'] = $directory;
        $emailArray['type'] = $type;

        if (($timestamp = strtotime($mail->date)) === false) {
            $emailArray['date'] = $mail->date;
        } else {
            $emailArray['date'] = date("Y-m-d H:i:s", $timestamp);
        }

        if (isset($mail->subject)) {
            $emailArray['subject'] = $mail->subject;
        } else {
            $emailArray['subject'] = "";
        }
        if (strpos($mail->from, '<') !== false) {
            $emailArray['fromAddress'] = self::getStringBetween($mail->from, "<", ">");
        } else {
            $emailArray['fromAddress'] = $mail->from;
        }

        $emailArray['fromName'] = trim(str_replace("<" . $emailArray['fromAddress'] . ">", "", $mail->from));

        if (isset($mail->to)) {
            $emailArray['to'] = $mail->to;
            $emailArray['toString'] = $mail->to;
        } else {
            $emailArray['to'] = '';
            $emailArray['toString'] = '';
        }

        if (isset($mail->cc)) {
            $emailArray['cc'] = $mail->cc;
        } else {
            $emailArray['cc'] = '';
        }
        if (isset($mail->replyto)) {
            $emailArray['replyTo'] = $mail->replyto;
        } else {
            $emailArray['replyTo'] = '';
        }
        if (isset($mail->messageid)) {
            $emailArray['messageId'] = $mail->messageid;
        } else {
            $emailArray['messageId'] = '';
        }
        $emailArray['textPlain'] = '';
        $emailArray['textHtml'] = '';
        $attachmentDetails = array();
        $emailArray['headers'] = array();

        try {
            foreach ($mail->getHeaders() as $header) {
                $emailArray['headers'][$header->getFieldName()] = $header->getFieldValue();
            }
        } catch (\Exception $ex) {
            Logger::error_logger($ex);
        }

        if ($mail->isMultipart()) {
            foreach (new \RecursiveIteratorIterator($mail) as $part) {
                if (strtok($part->contentType, ';') == 'text/plain') {
                    $emailArray['textPlain'] = $part->getContent();
                } else if (strtok($part->contentType, ';') == 'text/html') {
                    $contentTransferEncoding = $part->getHeader("Content-Transfer-Encoding")->getFieldValue();
                    $emailArray['textHtml'] = self::contentTransferDecode($part);
                } else {
                    if (strlen($part->getHeader("contentType")->getParameter("name")) > 0) {
                        $filename = $part->getHeader("contentType")->getParameter("name");
                        $contentTransferEncoding = $part->getHeader("Content-Transfer-Encoding")->getFieldValue();
                        $attachment = self::contentTransferDecode($part);
                        $fh = fopen($attachmentDir . '/' . $emailArray['m_id'] . "-" . $filename, 'w');
                        fwrite($fh, $attachment);
                        fclose($fh);
                        array_push($attachmentDetails, $attachmentDir . '/' . $emailArray['m_id'] . "-" . $filename);
                    }
                }
            }
        } else {
            $emailArray['textHtml'] = self::contentTransferDecode($mail);
        }
        $emailArray['attachments'] = json_encode($attachmentDetails);
        try {
            $emailArray['In-Reply-To'] = trim(str_replace("In-Reply-To:", "", $mail->getHeader('In-Reply-To')->toString()));
        } catch (\Exception $ex) {
            Logger::debug_logger($ex->getMessage());
        }
        return $emailArray;
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       May 23, 2016
     * @brief      Returns the string between 2 substrings.
     * @param      $string  String
     * @param      $start  Starting substring
     * @param      $end  Ending substring
     * @return     Substring between the 2 strings
     */
    private static function getStringBetween($string, $start, $end) {
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0)
            return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       May 23, 2016
     * @brief      Corrects the index based on gmail
     * @param      $storedArray  Stored Array
     * @param      $fetchedArray  Fetched
     * @return     Corrected array
     */
    public static function gmailArrayIndexCorrection($storedArray, $fetchedArray) {
        $indexCorrectedStoredArray = array();
        foreach ($fetchedArray as $fetchedKey => $fetchedValue) {
            foreach ($storedArray as $storedKey => $storedValue) {
                if ($storedValue == $fetchedValue) {
                    $indexCorrectedStoredArray[$fetchedKey] = $fetchedValue;
                }
            }
        }
        $deletedArray = array_diff($storedArray, $indexCorrectedStoredArray);
        $newArray = array_diff($fetchedArray, $indexCorrectedStoredArray);
        return array('NewArray' => $newArray,
            'DeletedArray' => $deletedArray);
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       October 18, 2016
     * @brief      Generates search query array
     * @param      $searchQuery  Array of search parameters
     * @return     Search query array
     */
    public static function generateSearchQuery($searchParams) {
        $searchQuery = array();
        if (isset($searchParams['mailids'])) {
            foreach ($searchParams['mailids'] as $mailId) {
                array_push($searchQuery, "FROM " . $mailId);
                array_push($searchQuery, "TO " . $mailId);
            }
        }
        if (isset($searchParams['sentmail_uid'])) {
            array_push($searchQuery, 'HEADER sx-message-id "' . $searchParams['sentmail_uid'] . '"');
        }
        return $searchQuery;
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       October 19, 2016
     * @brief      Splits up the accounts into batches for parallel processing.
     * @param      $params   Account details.
     * @return     Batch data
     */
    public static function generateBatchData($accounts, $batchSize) {
        $start = 0;
        $limit = $batchSize;
        $count = 0;
        $batchNumber = 1;
        $batchData[$batchNumber] = array();
        foreach ($accounts as $account) {
            $count++;
            $start++;
            array_push($batchData[$batchNumber], $account);
            if ($start == $limit) {
                $batchNumber++;
                $limit+=$batchSize;
                $batchData[$batchNumber] = array();
            }
        }
        return $batchData;
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       October 19, 2016
     * @brief      Checks Imap credentials.
     * @param      $payload   payload data
     * @return     Boolean
     */
    public static function checkImapCredentials($payload) {
        try {
            $imap = new Imap();
            $imap->connect($payload['imap_host'], $payload['imap_port'], $payload['imap_encryption']);
            $imap->login($payload['user_name'], $payload['password']);
        } catch (\Exception $e) {
            Logger::error_logger("Error :" . $e->getMessage());
            return false;
        }
        return true;
    }

    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       October 19, 2016
     * @brief      Encrypt SMTP-IMAP Password.
     * @param      $payload   Payload data.
     * @return     Encrypted data.
     */
    public static function passwordEncrypt($payload) {
        $encryptKey = Constants::$ENCRYPTION_KEY;
        $salt = substr(md5(mt_rand(), true), 8);
        $key = md5($encryptKey . $salt, true);
        $iv = md5($key . $encryptKey . $salt, true);
        $ct = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $payload, MCRYPT_MODE_CBC, $iv);
        return base64_encode('Salted__' . $salt . $ct);
    }

    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       October 19, 2016
     * @brief      Decrypt SMTP-IMAP Password.
     * @param      $payload   Payload Encrypt password data.
     * @return     password
     */
    public static function passwordDecrypt($payload) {
        $encryptKey = Constants::$ENCRYPTION_KEY;
        $data = base64_decode($payload);
        $salt = substr($data, 8, 8);
        $ct = substr($data, 16);
        $key = md5($encryptKey . $salt, true);
        $iv = md5($key . $encryptKey . $salt, true);
        $pass = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $ct, MCRYPT_MODE_CBC, $iv);
        return $pass;
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       October 26, 2016
     * @brief      Add email metadata.
     * @param      $mailKeyArray mail Id array
     */
    public static function addEmailMetadata($mailKeyArray, $mailAddress, $userId, $directory) {
        $mailContent = new Email_Content('Email_Content');
        $mailMetaArray = [
            "user_id" => (Integer) $userId,
            "sx_imap_id" => $mailAddress,
            "directory" => $directory,
            "mailKeyArray" => $mailKeyArray];
        $mailContent->saveEmail($mailMetaArray);
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       October 26, 2016
     * @brief      Trim mail id array to fetch mails within the set limit.
     * @param      $mailsIds Mail Id array
     * @param      $skip     Number of mails to skip
     * @param      $limit    Limit
     */
    public static function trimMailIdArray($mailsIds, $skip, $limit, $mailAddress, $userId, $directory) {
        $datasetSize = sizeof($mailsIds);
        $limitSize = $limit - $skip;
        if ($limitSize > $datasetSize) {
            $mailArrays['trimmed_array'] = $mailsIds;
            $mailArrays['rejected_array'] = array();
            ;
            return $mailArrays;
        }
        end($mailsIds);
        $key = key($mailsIds);

        $trimmedArray = array();
        $rejectedArray = array();
        $counter = 0;
        for ($i = $key; $i >= 0; $i--) {
            if (isset($mailsIds[$i])) {
                if ($counter > $skip && $counter <= $limit) {
                    array_push($trimmedArray, $mailsIds[$i]);
                } else {
                    array_push($rejectedArray, $mailsIds[$i]);
                }
                $counter ++;
            }
        }
        self::addEmailMetadata($rejectedArray, $mailAddress, $userId, $directory);
        $mailArrays['trimmed_array'] = $trimmedArray;
        $mailArrays['rejected_array'] = $rejectedArray;
        return $mailArrays;
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       November 9, 2016
     * @brief      Checks SMTP credentials
     * @param      $payload Payload data
     */
    public static function checkSMTPCredentials($payload) {
        try {
            $config = null;
            if (strlen($payload['smtp_encryption']) > 0) {
                $config = array('ssl' => $payload['smtp_encryption']);
            }
            $config['username'] = $payload['user_name'];
            $config['password'] = $payload['password'];
            $smtp = new Login($payload['smtp_host'], $payload['smtp_port'], $config);
            $smtp->connect();
            $smtp->helo();
            return true;
        } catch (\Exception $ex) {
            Logger::error_logger($ex->getMessage());
            Logger::error_logger($ex->getLine());
            Logger::error_logger($ex->getFile());
            return false;
        }
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       November 9, 2016
     * @brief      Checks if mail id already exists
     * @param      $payload Payload data
     */
    public static function mailIdExists($userId, $userName) {
        $imapSmtpCredentials = new Imap_Smtp_Credentials();
        return $imapSmtpCredentials->mailIdExists($userId, $userName);
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       November 10, 2016
     * @brief      Fetches mail user data.
     * @param      $payload Payload data
     */
    public static function fetchMailUserData($emailArray, $orgId) {
        $mailUserData['mail_to'] = self::formatMailIdData($emailArray['to'], $orgId);
        $mailUserData['mail_from'] = self::formatMailIdData($emailArray['fromAddress'], $orgId, $emailArray['fromName']);
        if (strlen($emailArray['cc']) > 0) {
            $mailUserData['mail_cc'] = self::formatMailIdData($emailArray['cc'], $orgId);
        }
        return $mailUserData;
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       November 10, 2016
     * @brief      Formats the mail details.
     * @param      $payload Payload data
     */
    public static function formatMailIdData($emailArray, $orgId, $pickedName = "") {
        $resultArray = array();
        $mailTo = explode(",", trim($emailArray));
        foreach ($mailTo as $key => $toId) {
            $emailId = trim($toId);
            if (strpos($emailId, '<') !== false) {
                $pickedName = explode("<", $emailId);
                $pickedName = trim($pickedName[0]);
                $emailId = trim(self::getStringBetween($emailId, "<", ">"));
            }
            $resultArray[$key]['mail_id'] = $emailId;
            $contactModel = new Contact();
            $contactDetails = $contactModel->fetchContactByMailId($emailId, $orgId);
            if ($contactDetails != null) {
                $resultArray[$key]['server_id'] = (Integer) $contactDetails['id'];
                $resultArray[$key]['type'] = 'CONTACT';
                $resultArray[$key]['name'] = $contactDetails['name'];
            } else {
                $useProfileModel = new User_Profile();
                $userProfileData = $useProfileModel->fetchUserByMailId($emailId, $orgId);
                if ($userProfileData != null) {
                    $resultArray[$key]['server_id'] = (Integer) $userProfileData['user_id'];
                    $resultArray[$key]['type'] = 'USER';
                    $resultArray[$key]['name'] = $userProfileData['first_name'] . " " . $userProfileData['last_name'];
                } else {
                    $resultArray[$key]['server_id'] = 0;
                    $resultArray[$key]['type'] = 'UNKNOWN';
                    $resultArray[$key]['name'] = $pickedName;
                }
            }
        }
        return $resultArray;
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       November 16, 2016
     * @brief      Adds mail reminder data to db
     * @param      $mailReminder Number of days
     * @param      $emailArray Email Array
     */
    public static function addMailReminderData($mailReminder, $emailArray) {
        $emailReminderModel = new Email_Reminders();
        $data['user_id'] = $emailArray['user_id'];
        $data['message_id'] = $emailArray['messageId'];
        $data['sxuid'] = $emailArray['sxuid'];
        $data['email_settings_id'] = $emailArray['sx_account_id'];
        $data['date'] = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') + $mailReminder, date('Y')));

        $emailReminderModel->addEmailReminder($data);
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       November 16, 2016
     * @brief      Updates mail reminder data
     * @param      $mailReminder Number of days
     * @param      $emailArray Email Array
     */
    public static function updateReplyReminder($emailArray) {
        $data['user_id'] = $emailArray['user_id'];
        $data['message_id'] = $emailArray['In-Reply-To'];
        $data['email_settings_id'] = $emailArray['sx_account_id'];
        $emailReminderModel = new Email_Reminders();
        $emailReminderModel->updateReminder($data);
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       November 29, 2016
     * @brief      Normalize flag keys
     * @param      $mailReminder Number of days
     * @param      $emailArray Email Array
     */
    public static function normalizeFlagKeys($tempFlags) {
        $flagArray = array();
        foreach ($tempFlags as $flag) {
            $flagStrip = str_replace('\\', '', $flag);
            $flagArray[$flagStrip] = $flag;
        }
        return $flagArray;
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       November 30, 2016
     * @brief      Mail quota check.
     * @param      $userId User Id
     */
    public static function mailQuotaCheck($userId) {
        $mailBox = new Mailbox();
        $mailDbcount = $mailBox->fetchMailboxSize($userId);

        $mailDbSize = ($mailDbcount * 50) / 1000;

        $directory = getenv('APPLICATION.PATH') . "/application/files/emailattachments/" . $userId;

        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));
        $bytes = 0;
        foreach ($iterator as $i) {
            $bytes += $i->getSize();
        }
        $Mbytes = $bytes / 1000 / 1000;

        $total = $mailDbSize + $Mbytes;
        if ($total >= getenv('MAIL.QUOTA.LIMIT')) {
            return false;
        }
        return true;
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       December 08, 2016
     * @brief      Mail content transfer decoding
     * @param      $message Mail message
     * @param      $encoding Encoding
     */
    public static function contentTransferDecode($message) {
        try {
            $encoding = $message->getHeader("Content-Transfer-Encoding")->getFieldValue();
        } catch (\Exception $ex) {
            return $message->getContent();
        }
        $encoding = strtoupper($encoding);
        switch ($encoding) {
            case '7BIT': return $message->getContent();
            case '8BIT': return $message->getContent();
            case 'BINARY': return $message->getContent();
            case 'BASE64': return base64_decode($message->getContent());
            case 'QUOTED-PRINTABLE': return quoted_printable_decode($message->getContent());
            default : return $message->getContent();
        }
    }

}
