<?php

use Slim\App as App;

/**
 * @author     Nikhil N R, <nikhil@salesx.io>
 * @date       May 10, 2016
 * @brief      Unit Test Class for ReceiveGoogleEmail.php
 * @details    Covers all the test cases for ReceiveGoogleEmail
 */
class SendGoogleEmailTest extends PHPUnit_Framework_TestCase {

    protected function setUp() {
        $_SESSION["user_id"] = 18;
        $app = new App([
            'mode' => file_get_contents('../mode.ini')
        ]);

        $dotenv = new Dotenv\Dotenv('../application/config', $app->getContainer()->mode . '.env');
        $dotenv->load();
        require '../application/config/database.php';
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       April 29, 2016
     * @brief      Incorrect fields
     * @details    Checks if the error response is returned when incorrect fields
     *             are passed
     */
    public function testsendEmail() {
        $mailMessage = new library\SX\Email\SXMailMessage();
        $mailMessage->setFrom('nikhil@salesx.io');
        $mailMessage->setTo("mail.nikhil.n.r@gmail.com");
        $mailMessage->setSubject('Html mail');
        $mailMessage->setAltBody('Alt body');
        $mailMessage->setIsHTML(true);
        $mailMessage->setBody('<html><b>some body text</b></html>');
        \library\SX\Email\SendGoogleEmail::sendEmail($mailMessage);
    }

}
