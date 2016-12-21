<?php
/**
 * @author     Nikhil N R, <nikhil@salesx.io>
 * @date       April 29, 2016
 * @brief      Unit Test Class for SignupManager.php
 */
class SignupManagerTest extends PHPUnit_Framework_TestCase {

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
    public function testsignupAction_credentails_returnsSuccessJson() {
        $payload = ['email' => library\SX\Hash\HashManager::generateEncryptionKeymap(),
            'password' => 'password',
            'organisation' => library\SX\Hash\HashManager::generateEncryptionKeymap(),
            'firstname' => 'firstname',
            'lastname' => 'lastname',
            'role_id' => 1];
        $result = json_decode(library\SX\Signup\SignupManager::signupAction($payload),true);

        $this->assertTrue(array_key_exists('success', $result));
        $this->assertTrue(array_key_exists('user_id', $result));
        $this->assertTrue(array_key_exists('organization_id', $result));
        $this->assertEquals('true', $result['success']);
    }

}
