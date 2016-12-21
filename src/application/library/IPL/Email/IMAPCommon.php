<?php

/**
 * @author     Nikhil N R, <nikhil@salesx.io>
 * @date       November 29, 2016
 * @brief      This class is used for common IMAP functions
 */

namespace library\SX\Email;

use \Zend\Mail\Storage\Imap as Storage;
use \Zend\Mail\Protocol\Imap as Imap;
use \models\Mailbox as Mailbox;
use \library\SX\Email\Utils as EmailUtils;
use \library\SX\Common\Logger as Logger;

class IMAPCommon {

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       November 29, 2016
     * @brief      Edit //Seen flag
     * @param      $imap   Imap object
     * @param      $payload   Payload
     */
    public static function markAsRead($imap, $payload) {
        $imapExamine = $imap->examine();
        $seenFlagKey = '\Seen';
        if (isset($imapExamine['flags'][0])) {
            foreach ($imapExamine['flags'][0] as $serverFlag) {
                if (strpos($serverFlag, 'Seen') !== false) {
                    $seenFlagKey = $serverFlag;
                    Logger::debug_logger("Seen key from server - " . $seenFlagKey);
                }
            }
        }

        $storage = new Storage($imap);

        Logger::debug_logger("selecting INBOX");
        try {
            $storage->selectFolder('INBOX');
        } catch (\Exception $e) {
            Logger::error_logger($e->getMessage());
        }
        $mid = $storage->getNumberByUniqueId($payload['m_id']);
        $mail = $storage->getMessage($mid);
        $flags = $mail->getFlags();

        if ($payload['read'] == 1) {
            $flags[$seenFlagKey] = $seenFlagKey;
        } else {
            if (isset($flags[$seenFlagKey])) {
                unset($flags[$seenFlagKey]);
            }
        }
        $storage->setFlags($mid, $flags);

        $flags = EmailUtils::normalizeFlagKeys($flags);

        $mailBoxModel = new Mailbox();
        $mailBoxModel->updateEmailFlags($payload['sxuid'], $flags);

        $response['success'] = "true";
        $response['message'] = "Seen flag updated";
        return json_encode($response, JSON_NUMERIC_CHECK);
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       November 30, 2016
     * @brief      Delete email
     * @param      $imap   Imap object
     * @param      $payload   Payload
     */
    public static function deleteEmail($imap, $payload) {
        $storage = new Storage($imap);
        try {
            $storage->selectFolder($payload['directory']);
            $mid = $storage->getNumberByUniqueId($payload['m_id']);
            $storage->removeMessage($mid);
            $mailBoxModel = new Mailbox();
            $mailBoxModel->deleteMail($payload['sxuid']);
        } catch (\Exception $e) {
            $response['success'] = "false";
            $response['message'] = $e->getMessage();
            return json_encode($response, JSON_NUMERIC_CHECK);
        }
        $response['success'] = "true";
        $response['message'] = "Mail deleted";
        return json_encode($response, JSON_NUMERIC_CHECK);
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       December 2, 2016
     * @brief      Fetch Inbox unread count
     * @param      $imap   Imap object
     * @param      $payload   Payload
     */
    public static function fetchInboxUnreadCount(Imap $imap, $payload) {
        $storage = new Storage($imap);
        Logger::debug_logger("selecting INBOX");
        try {
            $storage->selectFolder('INBOX');
        } catch (\Exception $e) {
            Logger::error_logger($e->getMessage());
        }
        $imap->select('INBOX');
        $mailsIds = $imap->search(array("UNSEEN"));
        $response['success'] = "true";
        $response['count'] = sizeof($mailsIds);
        return json_encode($response, JSON_NUMERIC_CHECK);
    }

}
