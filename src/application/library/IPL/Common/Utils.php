<?php

/**
 * @author     Ajith E R, <ajith@salesx.io>
 * @date       October 16, 2016
 * @brief      This class is used for Utilities 
 */

namespace library\IPL\Common;

use \models\User_Profile as User_Profile;

class Utils {

    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       October 16, 2016
     * @brief      User Profile Picture Mime Type.
     * @param      $data  Base64 encodeded string.
     * @return     Mime Type jpeg , png ...
     */
    public static function findMimeType($data) {
        $f = finfo_open();
        $mime_type = finfo_buffer($f, $data, FILEINFO_MIME_TYPE);
        $split = explode('/', $mime_type);
        return $split[1];
    }

    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       October 18, 2016
     * @brief      User Profile Picture Upload Type.
     * @param      $data  Base64 encodeded string.
     * @return     Mime Type jpeg , png ...
     */
    public static function photoUpload($data) {
        $contactProfileModel = new Contact();
        $userProfileModel = new User_Profile();
        $data['user_id'] = $_SESSION["user_id"];
        $image = self::decodeBase64Image($data);
        $data['image'] = $image;
        $type = self::findMimeType($image);
        $fname = self::photoFileName($data);
        $data['filename'] = $fname . '.' . $type;
        $response['success'] = "true";
        $response['path'] = $data['filename'];
        $response['message'] = "Photo Uploaded.";
        $error['success'] = "false";
        $error['message'] = "Photo Upload failed.";
        if (isset($data['type']) && $data['type'] == "contact") {
            $result = self::photoSave($data);
            if ($result['success'] == 'true') {
                $contactProfileModel->editContactPhoto($data);
                return json_encode($response, JSON_NUMERIC_CHECK);
            } else {
                return json_encode($error, JSON_NUMERIC_CHECK);
            }
        } else if ($data['type'] == "user") {
            $result = self::photoSave($data);
            if ($result['success'] == 'true') {
                $userProfileModel->editUserPhoto($data);
                return json_encode($response, JSON_NUMERIC_CHECK);
            } else {
                return json_encode($error, JSON_NUMERIC_CHECK);
            }
        }
    }

    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       October 18, 2016
     * @brief      Decode Base64 Data.
     * @param      $data  Base64 encodeded string.
     * @return     Mime Type jpeg , png ...
     */
    public static function decodeBase64Image($data) {
        return base64_decode($data['photo']);
    }

    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       October 18, 2016
     * @brief      Generate File Name.
     * @param      contact_id or user_id.
     * @return     filename
     */
    public static function photoFileName($data) {
        if (isset($data['user_id']) && $data['type'] == "user") {
            $id = $data['user_id'];
        } elseif (isset($data['contact_id']) && $data['type'] == "contact") {
            $id = $data['contact_id'];
        }
        $date = date('Y-m-d h:i:s', time());
        $fname = preg_replace(
                array('/-/', '/ /', '/:/'), array(''), $date
        );
        return $id . "_" . $fname;
    }

    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       October 18, 2016
     * @brief      Save photo to folder.
     * @param      payload.
     * @return     $response
     */
    public static function photoSave($data) {
        $userProfileModel = new User_Profile();
        $contactProfileModel = new Contact();
        $fname = $data['filename'];
        $image = $data['image'];
        if (isset($data['user_id']) && $data['type'] == "user") {
            $link = $userProfileModel->userPhotoUrl($data['user_id']);
            $profilePicDirectory = getenv('APPLICATION.PATH') . "/application/files/userprofilepic";
        } else if (isset($data['contact_id']) && $data['type'] == "contact") {
            $link = $contactProfileModel->contactPhotoUrl($data);
            $profilePicDirectory = getenv('APPLICATION.PATH') . "/application/files/contactprofilepic";
        }
        if (file_exists($profilePicDirectory . '/' . $link)) {
            unlink($profilePicDirectory . '/' . $link);
        }
        if (!file_exists($profilePicDirectory)) {
            mkdir($profilePicDirectory);
        }
        $save = file_put_contents($profilePicDirectory . "/" . $fname, $image);
        if ($save !== FALSE) {
            $response['success'] = "true";
            $response['message'] = "User Profile Pic Saved.";
            return $response;
        } else {
            $response['success'] = "false";
            $response['message'] = "Somthing Went Wrong.";
            return $response;
        }
    }

    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       October 20, 2016
     * @brief      Random Password for Invite user.
     * @param      
     * @return     $password
     */
    public static function generatePassword() {
        $keys = array_merge(range(0, 9), range('a', 'z'));
        $length = 8;
        $key = "";
        for ($i = 0; $i < $length; $i++) {
            $key .= $keys[mt_rand(0, count($keys) - 1)];
        }
        return $key;
    }

    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       December 19, 2016
     * @brief
     */
    public static function fetchImage($queryParams) {
        if ($queryParams['type'] == 'profile') {
            $file = getenv('APPLICATION.PATH') . '/application/files/userprofilepic/' . $queryParams['filename'];
        } else {
            $file = getenv('APPLICATION.PATH') . '/application/files/contactprofilepic/' . $queryParams['filename'];
        }
        $filesize = filesize($file);
        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: private', false);
        header('Content-Disposition: attachment; filename="' . $queryParams['filename'] . '"');
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: ' . $filesize);
        readfile($file);
    }
}
