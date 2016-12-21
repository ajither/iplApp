<?php

/**
 * @author     Nikhil N R, <nikhil@salesx.io>
 * @date       May 10, 2016
 * @brief      Unit Test Class for IMAPHandler.php
 * @details    Covers all the test cases for ReceiveEmail
 */
use \Zend\Mail\Storage\Imap as Storage;
use \Zend\Mail\Protocol\Imap as Imap;

class IMAPHandlerTest extends PHPUnit_Framework_TestCase {

    protected function setUp() {
        $_SESSION["user_id"] = 18;
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
     * @brief      Fetch all mail
     */
    public function testfetchAllMail() {
        library\SX\Email\OtherIMAPHandler::syncAllMail('{imap.mail.yahoo.com:993/imap/ssl}', 'nikhil.nr@yahoo.com', 'salesx@9895', 18);
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       November 8, 2016
     * @brief      Update sent mail
     */
    public function testUpdateSentMail() {

        $argumentArray = Array(
            'type' => 'OTHER',
            'unique_id' => '58216e9c10bf8',
            'sx_account_id' => 5,
            'imap_data' => Array
                (
                'id' => 9,
                'user_name' => 'nikhil.nr@yahoo.com',
                'password' => 'salesx@9895',
                'imap_port' => 993,
                'imap_host' => 'imap.mail.yahoo.com',
                'imap_ssl' => 1,
                'smtp_port' => 465,
                'smtp_host' => 'plus.smtp.mail.yahoo.com',
                'smtp_ssl' => 1,
                'initial_fetch' => 1
            ),
            'mail_address' => 'nikhil.nr@yahoo.com',
            'imap_id' => 9,
            'user_id' => 38
        );

        \library\SX\Email\OtherIMAPHandler::updateSentMail($argumentArray);
    }

}
