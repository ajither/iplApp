<?php

/**
 * @author     Nikhil N R, <nikhil@salesx.io>
 * @date       April 29, 2016
 * @brief      Unit Test Class for Validations.php
 * @details    Covers all the test cases for Validations
 */

class ValidationsTest extends PHPUnit_Framework_TestCase {

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       April 29, 2016
     * @brief      Incorrect fields
     * @details    Checks if the error response is returned when incorrect fields
     *             are passed
     */
    public function testvalidateMandatoryFields_incorrectParams_returnsSuccess() {
        $expectedFields = ["user_name", "user_password", "user_ip"];
        $payLoad = ["user_name" => "uname", "user_password" => "upassword"];
        $result = \library\SX\Common\Validations::
                validateMandatoryFields($expectedFields, $payLoad);
        $this->assertFalse($result['status']);
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       April 29, 2016
     * @brief      Correct fields
     * @details    Checks if the correct response is returned when correct fields
     *             are passed
     */
    public function testvalidateMandatoryFields_correctParams_returnsFailure() {
        $expectedFields = ["user_name", "user_password", "user_ip"];
        $payLoad = ["user_name" => "uname", "user_password" => "upassword", "user_ip" => "usr_ip"];
        $result = \library\SX\Common\Validations::
                validateMandatoryFields($expectedFields, $payLoad);
        $this->assertTrue($result['status']);
    }

}
