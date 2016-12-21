<?php

/**
 * @author     Nikhil N R, <nikhil@salesx.io>
 * @date       June 27, 2016
 * @brief      User_Profile Model
 */

namespace models;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Capsule\Manager as Capsule;

class User_Profile extends Eloquent {

    public $timestamps = false;
    protected $table = 'User_Profile';
    protected $fillable = [
        'user_id',
        'first_name',
        'middle_name',
        'last_name',
        'profile_photo',
        'designation',
        'mail_id',
        'contact_numbers',
        'salesx_number',
        'record_calls',
        'number_verified',
        'organization_id'
    ];

    public function __construct() {
        $this->tableObject = $this->getConnectionResolver()->connection()->table($this->table);
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       June 27, 2016
     * @brief      Add User Profile.
     * @param      $data  Data array.
     * @return     Insert operation output
     */
    public function addUserProfile($data) {
        return $this->tableObject->insertGetId($data);
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       June 27, 2016
     * @brief      Fetch User Profile
     * @param      $userId Id
     * @return     Pipeline details
     */
    public function fetchUserProfile($userId) {
        $result = $this->tableObject->
                where('user_id', $userId)->
                join("User", "User.id", "=", "User_Profile.user_id")->
                join("User_Roles", "User_Roles.id", "=", "User.role_id")->
                get(array('User_Profile.user_id', 'User_Profile.first_name',
            'User_Profile.middle_name',
            'User_Profile.last_name',
            'User_Profile.profile_photo',
            'User_Profile.designation',
            'User_Profile.mail_id',
            'User_Profile.contact_numbers',
            'User_Profile.salesx_number',
            'User_Profile.record_calls',
            'User_Profile.number_verified',
            'User.role_id as role_id',
            'User_Roles.role as role'
        ));
        if (sizeof($result) > 0) {
            $resultArray = json_decode(json_encode($result[0]), true);
            foreach ($resultArray as $key => $values) {
                if ($values == null) {
                    $resultArray[$key] = "";
                }
            }
            return $resultArray;
        } else {
            return "";
        }
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       June 27, 2016
     * @brief      Delete User Profile
     * @param      $userId Id
     * @return     Result of the delete operation
     */
    public function deleteUserProfile($userId) {
        $result = $this->tableObject->
                where('user_id', $userId)->
                delete();
        return $result;
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       July 11, 2016
     * @brief      Edit User Profile.
     * @param      $data  Data array.
     * @return     Insert operation output
     */
    public function editUserProfile($data) {
        return $this->tableObject->
                        where('user_id', $data['id'])->
                        update($data);
    }

    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       October 12, 2016
     * @brief      Edit User Profile Picture.
     * @param      $data  Data array.
     * @return     Insert operation output
     */
    public function editUserPhoto($data) {
        $save['user_id'] = $data['user_id'];
        $save['profile_photo'] = $data['filename'];
        return $this->tableObject->
                        where('user_id', $save['user_id'])->
                        update($save);
    }

    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       October 13, 2016
     * @brief      Fetch User Old Profile Picture url.
     * @param      $data  user_id.
     * @return     profile picture folder path.
     */
    public function userPhotoUrl($data) {
        $fetch = $this->tableObject->
                        where('user_id', $data)->get(array('profile_photo'));
        if (sizeof($fetch) > 0) {
            $resultArray = json_decode(json_encode($fetch[0]), true);
            foreach ($resultArray as $key => $values) {
                if ($values == null) {
                    $resultArray[$key] = "";
                }
            }
            return $resultArray['profile_photo'];
        }
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       November 11, 2016
     * @brief      Fetches contacts by mail id.
     * @param      $id  Contact Id
     * @return     Result of delete operation
     */
    public function fetchUserByMailId($emailId, $orgId) {
        $result = $this->tableObject->
                where('mail_id', 'LIKE', "%$emailId%")->
                where('organization_id', $orgId)->
                get(array('user_id', 'first_name', 'last_name'));
        $result = json_decode(json_encode($result), true);
        if (isset($result[0])) {
            return $result[0];
        } else {
            return null;
        }
    }

}
