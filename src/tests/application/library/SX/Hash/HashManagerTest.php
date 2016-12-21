<?php

/**
 * @author     Nikhil N R, <nikhil@salesx.io>
 * @date       April 29, 2016
 * @brief      Unit Test Class for HashManager.php
 */
class HashManagerTest extends PHPUnit_Framework_TestCase {

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       April 29, 2016
     * @brief      Password to be hashed
     * @details    Checks if the returned hashed password is correct.
     */
    public function testpasswordHash_password_returnsHashedPassword() {
        $hashedPassword = library\SX\Hash\HashManager::passwordHash("password");
        $this->assertEquals($hashedPassword, "5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8");
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       April 29, 2016
     * @brief      Checks a hash agaisnt a given password
     * @details    Correct password is given, it returns true.
     */
    public function testpasswordCheck_correctPassword_returnsTrue() {
        $result = library\SX\Hash\HashManager::
                passwordCheck("password", "5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8");
        $this->assertTrue($result);
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       April 29, 2016
     * @brief      Checks a hash agaisnt a given password
     * @details    Incorrect password is given, it returns false.
     */
    public function testpasswordCheck_incorrectPassword_returnsFalse() {
        $result = library\SX\Hash\HashManager::
                passwordCheck("wrong_password", "5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8");
        $this->assertFalse($result);
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       April 29, 2016
     * @brief      Checks the generated encryption key map
     * @details    Rules for a correct Encryption key map
     *             - Length should be equal to the passed length parameter.
     *             - It should only contain alphabets.
     *             - No repeating alphabets.
     */
    public function testgenerateEncryptionKeymap_length_returnsKeymap() {
        $length = 10;
        $keymap = library\SX\Hash\HashManager::generateEncryptionKeymap($length);
        $this->assertEquals($length, strlen($keymap)); //Length
        $this->assertRegExp("/^[A-z]+$/", $keymap); //Only Alphabets


        $repeatFlag = false;
        for ($i = 0; $i < strlen($keymap); $i++) {
            for ($j = $i; $j < strlen($keymap); $j++) {
                if ($i != $j && $keymap{$i} == $keymap{$j}) {
                    $repeatFlag = true;
                }
            }
        }
        $this->assertFalse($repeatFlag); // No alphabets are repeated
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       April 29, 2016
     * @brief      Incorrect Key map length is given
     * @details    Throws an exception with an error message.
     */
    public function testgenerateEncryptionKeymap_incorrectLength_throwsException() {
        $length = 100;
        try {
            $keymap = library\SX\Hash\HashManager::generateEncryptionKeymap($length);
        } catch (Exception $e) {
            $this->assertEquals("Impossible Keymap length", $e->getMessage());
        }
    }

}
