<?php

/**
 * @author     Nikhil N R, <nikhil@salesx.io>
 * @date       May 03, 2016
 * @brief      Unit Test Class for OAuthManager.php
 * @details    Covers all the test cases for OAuth Manager
 */

class OAuthManagerTest extends PHPUnit_Framework_TestCase {
    
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
     * @date       May 03, 2016
     * @brief      Incorrect fields
     * @details    Checks if url returned 
     */
    public function testgetAuthorizationCodeURL_correctCredentials_returnsUrl() {
        $oAuth = new \library\SX\OAuth\OAuthManager();
        $url = $oAuth->getAuthorizationCodeURL();
        $this->assertNotNull($url);
    }

}
