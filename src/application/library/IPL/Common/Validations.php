<?php

/**
 * @author     Ajith E R, <ajith@salesx.io>
 * @date       December 19, 2016
 * @brief      Api parameter validation class
 */

namespace library\IPL\Common;

class Validations {

    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       December 19, 2016
     * @brief      Validate Mandatory fields.
     */
    public static function validateMandatoryFields($expectedFields, $payLoad) {
        foreach ($expectedFields as $key) {
            if (($payLoad == null) || !array_key_exists($key, $payLoad)) {
                $response['status'] = false;
                $error['success'] = "false";
                $error['code'] = 501;
                $error['reason'] = 'Required field \'' . $key . '\' missing from payload';
                $response['body'] = $error;
                return $response;
            }
        }
        $response['status'] = true;
        return $response;
    }
}
