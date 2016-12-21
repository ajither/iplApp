<?php

/**
 * @author     Nikhil N R, <nikhil@salesx.io>
 * @date       April 29, 2016
 * @brief      Unit Test Class for LoginManager.php
 */
class LoginManagerTest extends PHPUnit_Framework_TestCase {

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
     * @date       April 29, 2016
     * @brief      Correct credentials are passed to the function.
     * @details    Respose is a success Json.
     */
    public function testloginAction_correctCredentails_returnsSuccessJson() {
        $payload = ['user_name' => 'mail2@salesx.io',
            'user_password' => 'password',
            'user_ip' => '127.0.0.1'];
        $result = json_decode(\library\SX\Login\LoginManager::loginAction($payload), true);
        $this->assertTrue(array_key_exists('success', $result));
        $this->assertTrue(array_key_exists('user_id', $result));
        $this->assertTrue(array_key_exists('session_token', $result));
        $this->assertEquals('true', $result['success']);
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       April 29, 2016
     * @brief      Incorrect credentials are passed to the function.
     * @details    Respose is a failure Json.
     */
    public function testloginAction_incorrectCredentails_returnsFailureJson() {
        $payload = ['user_name' => 'mail2@salesx.io',
            'user_password' => 'xx',
            'user_ip' => '127.0.0.1'];
        $result = json_decode(\library\SX\Login\LoginManager::loginAction($payload), true);
        $this->assertTrue(array_key_exists('success', $result));
        $this->assertTrue(array_key_exists('message', $result));
        $this->assertEquals('false', $result['success']);
        $this->assertEquals('Incorrect credentials', $result['message']);
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       April 29, 2016
     * @brief      Incorrect credentials are passed to the function.
     * @details    Boolean response - false.
     */
    public function testvalidateLoginCredentials_incorrectCredentails_returnsFalse() {
        $result = \library\SX\Login\LoginManager::validateLoginCredentials('mail2@salesx.io', 'xx');
        $this->assertFalse($result);
    }
    
    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       April 29, 2016
     * @brief      Correct credentials are passed to the function.
     * @details    Boolean response - true.
     */
    public function testvalidateLoginCredentials_correctCredentails_returnsTrue() {
        $result = \library\SX\Login\LoginManager::validateLoginCredentials('mail2@salesx.io', 'password');
        $this->assertTrue($result);
    }

}
