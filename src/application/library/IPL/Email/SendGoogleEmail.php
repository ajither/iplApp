<?php

/**
 * @author     Nikhil N R, <nikhil@salesx.io>
 * @date       May 10, 2016
 * @brief      This class is used sending Google emails
 */

namespace library\IPL\Email;

use \models\Google_Access_Tokens as Google_Access_Tokens;
use \library\SX\OAuth\OAuthManager as OAuthManager;
use \library\SX\Common\Logger as Logger;
use \library\SX\Common\Constants as Constants;
use \library\SX\Common\AsynchronousOperations as AsynchronousOperations;
use \Zend\Mail\Protocol\Smtp as Protocol;
use \Zend\Mail\Transport\Smtp as Transport;
use \Zend\Mail\Message as Message;
use \Zend\Mail\Header\GenericHeader as GenericHeader;
use \Zend\Mime\Part as MimePart;
use \Zend\Mime\Mime as Mime;
use \Zend\Mime\Message as MimeMessage;

class SendGoogleEmail {

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       May 10, 2016
     * @brief      Send emails from google.
     */
    public static function sendEmail(SXMailMessage $mailMessage, $userId, $googleToken, $uniqueId = null, $sxAccountId = null, $mailUserData = null, $smtpCred = null) {
        if ($smtpCred == null) {
            $access_token = $googleToken['google_access_token'];
            $client = OAuthManager::getGoogleClient();
            $client->setAccessToken($access_token);

            if ($client->isAccessTokenExpired()) {
                Logger::debug_logger("Google Authentication token expired. Refreshing token.");
                $access_token = OAuthManager::refreshAuthToken($client, $mailMessage->getFrom(), $userId);
            }
            $accesArray = json_decode($access_token, true);

            $config = array('ssl' => 'ssl');
            $smtp = new Protocol(Constants::$SMTP_GOOGLE_ADDRESS, Constants::$SMTP_GOOGLE_PORT, $config);
            $smtp->connect();

            if (!OAuthManager::oauth2Authenticate(null, $smtp, $mailMessage->getFrom(), $accesArray['access_token'])) {
                throw new \Exception("Google authentication failed", 500, null);
            }
        } else {
            $config = null;
            if (strlen($smtpCred['smtp_encryption']) > 0) {
                $config = array('ssl' => $smtpCred['smtp_encryption']);
            }
            $config['username'] = $smtpCred['user_name'];
            $config['password'] = Utils::passwordDecrypt($smtpCred['password']);
            $smtp = new Protocol\Auth\Login($smtpCred['smtp_host'], $smtpCred['smtp_port'], $config);
        }
        $transport = new Transport();
        $transport->setConnection($smtp);
        $message = new Message();
        $mailSettings = $mailMessage->getMailSettings();
        if ($mailSettings['mail_tracker'] == 1) {
            $tracker = getenv('APPLICATION.BASE_URL') . '/' . $_SESSION['api_version'] . '/emailopenrecord?sxuid=' . $uniqueId;
        } else {
            $tracker = "";
        }

        if ($mailMessage->getIsHTML()) {
            $content = new MimeMessage();
            $body = $mailMessage->getBody();
            $body = str_replace("</html>", "", $body);
            $body.='<img alt="" src="' . $tracker . '" width="1" height="1" border="0" /></html>';
            $html = new MimePart($body);
            $html->type = "text/html";
            $content->setParts(array($html));
            $contentPart = new MimePart($content->generateMessage());
            $contentPart->type = "text/html" . '; boundary="' . $content->getMime()->boundary() . '"';
        }
        $bodyArray = array();
        array_push($bodyArray, $contentPart);

        if (sizeof($mailMessage->getAttachment()) > 0) {
            $attachments = $mailMessage->getAttachment();
            foreach ($attachments as $attachmentArray) {
                $attachment = new MimePart(base64_decode($attachmentArray['content']));
                $attachment->type = $attachmentArray['type'];
                $attachment->encoding = Mime::ENCODING_BASE64;
                $attachment->disposition = Mime::DISPOSITION_ATTACHMENT;
                $attachment->filename = $attachmentArray['filename'];
                array_push($bodyArray, $attachment);
            }
        }

        $body = new MimeMessage();
        $body->setParts($bodyArray);

        $message->setBody($body);

        $message->setFrom($mailMessage->getFrom(), $mailMessage->getFromName());
        $message->addTo($mailMessage->getTo());
        if (sizeof($mailMessage->getCC()) > 0) {
            $message->addCc($mailMessage->getCC());
        }
        if (sizeof($mailMessage->getBCC()) > 0) {
            $message->addBcc($mailMessage->getBCC());
        }
        $message->setSubject($mailMessage->getSubject());

        $headers = $message->getHeaders();

        if (strlen($mailMessage->getReferences()) > 0) {
            $headers->addHeaderLine('References', $mailMessage->getReferences());
        }
        $headers->addHeader(GenericHeader::fromString("sx-message-id: " . $uniqueId));
        $transport->send($message);
        if ($mailSettings['mail_reminder'] == 1) {
            $argumentArray['mail_reminder'] = 1;
            $argumentArray['mail_reminder_date'] = $mailSettings['mail_reminder_date'];
        }
        $argumentArray['unique_id'] = $uniqueId;
        $argumentArray['sx_account_id'] = $sxAccountId;
        $argumentArray['user_id'] = $userId;
        $argumentArray['mail_user_data'] = $mailUserData;
        if ($googleToken != null) {
            $argumentArray['type'] = 'GMAIL';
            $argumentArray['from'] = $mailMessage->getFrom();
            $argumentArray['access_array'] = $accesArray;
            $argumentArray['token_id'] = $googleToken['id'];
        } else {
            $smtpCred['password'] = trim(Utils::passwordDecrypt($smtpCred['password']));
            $argumentArray['type'] = 'OTHER';
            $argumentArray['imap_data'] = $smtpCred;
            $argumentArray['mail_address'] = $smtpCred['user_name'];
            $argumentArray['imap_id'] = $smtpCred['id'];
        }
        $argString = json_encode($argumentArray);
        exec(getenv('PHP.LOCATION') . ' ' . getenv('APPLICATION.PATH') . '/scripts/updateSentMail.php ' . escapeshellarg($argString) . ' > /dev/null &');
    }

}
