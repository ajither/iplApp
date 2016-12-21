<?php

/**
 * @author     Nikhil N R, <nikhil@salesx.io>
 * @date       May 10, 2016
 * @brief      Unit Test Class for SendEmail.php
 * @details    Covers all the test cases for SendEmail
 */
class SendEmailTest extends PHPUnit_Framework_TestCase {

    protected function setUp() {
        $app = new Slim\App([
            'mode' => file_get_contents('../mode.ini')
        ]);

        $dotenv = new Dotenv\Dotenv('../application/config', $app->getContainer()->mode . '.env');
        $dotenv->load();
        require '../application/config/database.php';
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       May 10, 2016
     * @brief      Send mail
     */
    public function testsendEmail() {
        $mailMessage = new library\SX\Email\SXMailMessage();

        $smtpConfig = [
            'Host' => library\SX\Common\Constants::$SMTP_YAHOO_ADDRESS,
            'SMTPAuth' => true,
            'Username' => 'ammu_iyer007@yahoo.com',
            'Password' => 'password',
            'SMTPSecure' => 'ssl',
            'Port' => library\SX\Common\Constants::$SMTP_YAHOO_PORT
        ];

        $smtpConfig = [
            'Host' => 'plus.smtp.mail.yahoo.com',
            'SMTPAuth' => 1,
            'Username' => 'nikhil.nr@yahoo.com',
            'Password' => 'salesx@9895',
            'SMTPSecure' => 'ssl',
            'Port' => 465
        ];
        $mailMessage->setSMTPConfig($smtpConfig);
        $mailMessage->setFrom('nikhil.nr@yahoo.com');
        $mailMessage->setTo('nikhil@salesx.io');
        $mailMessage->setReplyTo('nikhil.nr@yahoo.com');
        $mailMessage->setIsHTML(true);
        $mailMessage->setSubject('Heres another one');
        $mailMessage->setBody('Message body');
        $mailMessage->setAltBody('Alt Message body');
        \library\SX\Email\SendEmail::sendEmail($mailMessage);
    }

}
