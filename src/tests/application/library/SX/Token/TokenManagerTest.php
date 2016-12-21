<?php

/**
 * @author     Nikhil N R, <nikhil@salesx.io>
 * @date       April 29, 2016
 * @brief      Unit Test Class for TokenManager.php
 */
class TokenManagerTest extends PHPUnit_Framework_TestCase {

    protected function setUp() {
        $_SESSION["user_id"] = 18;
        $_SESSION['access_log_string'] = "";
        $app = new Slim\App([
            'mode' => file_get_contents('../mode.ini')
        ]);

        $dotenv = new Dotenv\Dotenv('../application/config', $app->getContainer()->mode . '.env');
        $dotenv->load();
        require '../application/config/database.php';
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       April 29, 2016
     * @brief      Incorrect credentials are passed to the function.
     * @details    Respose is a failure Json.
     */
    public function testrefreshSessionToken_incorrectCredentails_returnsFailureJson() {
        $result = json_decode(library\SX\Token\TokenManager::
                refreshSessionToken('mail2@salesx.io', 'xxx'), true);

        $this->assertTrue(array_key_exists('success', $result));
        $this->assertTrue(array_key_exists('message', $result));
        $this->assertEquals('false', $result['success']);
        $this->assertEquals('Incorrect credentials', $result['message']);
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       April 29, 2016
     * @brief      Correct credentials are passed to the function.
     * @details    Respose is a success Json.
     */
    public function testrefreshSessionToken_correctCredentails_returnsSuccessJson() {
        $result = json_decode(library\SX\Token\TokenManager::
                refreshSessionToken('mail2@salesx.io', 'password'), true);

        $this->assertTrue(array_key_exists('success', $result));
        $this->assertTrue(array_key_exists('session_token', $result));
        $this->assertEquals('true', $result['success']);
    }
    
    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       April 29, 2016
     * @brief      Correct credentials are passed to the function.
     * @details    Boolean response True.
     */
    public function testgenerateSessionToken_correctCredentails_returnsTrue() {
        
        $sessionToken = library\SX\Token\TokenManager::
                generateSessionToken('mail12345687@salesx.io', 'password');
        $result = library\SX\Token\TokenManager::validateSessionToken($sessionToken);
        

        $this->assertTrue($result);
    }

}
