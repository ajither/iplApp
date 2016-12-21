<?php

/**
 * @author     Nikhil N R, <nikhil@salesx.io>
 * @date       April 30, 2016
 * @brief      This class is used sending emails
 */

namespace library\SX\Email;

use \library\SX\Common\Constants as Constants;
use \PhpImap\Mailbox as Mailbox;

class SendEmail {

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       November 8, 2016
     * @brief      Manually push sent mail into IMAP
     */
    public static function manualImapAppend($imapData, $mail) {
        $imapString = '{' . $imapData['imap_host'] . ':' . $imapData['imap_port'] . '/imap/';
        if ($imapData['imap_ssl'] == 1) {
            $imapString .= 'ssl';
        }
        $imapString .= '}';
        $mailBox = new Mailbox($imapString, $imapData['user_name'], Utils::passwordDecrypt($imapData['password']), "/");
        $stream = $mailBox->getImapStream();
        imap_append($stream, $imapString . 'Sent', $mail->getSentMIMEMessage(), "\\Seen");
    }

}
