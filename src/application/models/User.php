<?php
/**
 * @author     Ajith E R, <ajith@salesx.io>
 * @date       December 19, 2016
 * @brief      Sign up API
 */

namespace models;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Capsule\Manager as Capsule;

class User extends Eloquent {

    public $timestamps = false;
    protected $table = 'user';
    protected $fillable = [
        'username',
        'email',
        'password',
        'first_name',
        'last_name'
    ];

    public function __construct() {
        $this->tableObject = $this->getConnectionResolver()->connection()->table($this->table);
    }

    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       December 19, 2016
     * @brief      Sign up API
     */
    public function addUser($data) {
        return $this->tableObject->insertGetId($data);
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       April 26, 2016
     */
    public function fetchDetailsByEmail($email) {
        $result = $this->tableObject->
                        where('email', $email)->get();
        if (isset($result[0])) {
            return $result[0];
        } else {
            return null;
        }
    }

    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       October 31, 2016
     */
    public function fetchUserDetails($id) {
        $result = $this->tableObject->
                        where('id', $id)->get();
        if (isset($result[0])) {
            return $result[0];
        } else {
            return null;
        }
    }

    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       October 31, 2016
     */
    public function updateUser($data) {
        return $this->tableObject->
                        where('id', $data['id'])->
                        update($data);
    }


    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       October 31, 2016
     * @brief      Fetch USer Details
     */
    public function fetchDetailsByUsername($userName)
    {
        $result = $this->tableObject->
        where('email', $userName)->orWhere('username', $userName)->get();
        if (isset($result[0])) {
            return $result[0];
        } else {
            return null;
        }
    }

    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       October 31, 2016
     * @brief      Fetch USer Details
     */
    public function editUser($data)
    {
        return $this->tableObject->where('email', $data['email'])->
        update($data);
    }

    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       October 31, 2016
     * @brief      Fetch USer Details
     */
    public function fetchAllDetailsByUsername($userName)
    {
        $result = $this->tableObject->
        where('email', $userName)->orWhere('username', $userName)->
        join("user_profile","user.user_id","=","user_profile.user_id")->
            join("user_refferal","user.user_id","=","user_refferal.user_id")->
        get(array("user.user_id as userid","user.email","user.first_name as firstname","user.last_name as lastname","user.username","user_profile.profile_picture as profilepicture","user_profile.refferal_code as refferalcode","user_profile.phone_number as phonenumber", "user_refferal.refferal_point as refferalpoint","user_refferal.refferal_users_count as refferalusercount"));
        if (isset($result[0])) {
            return $result[0];
        } else {
            return null;
        }
    }

    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       October 31, 2016
     * @brief      Fetch USer Details by UserId
     */
    public function fetchAllDetailsByUserId($userId)
    {
        $result = $this->tableObject->
        where('user.user_id', $userId)->
        join("user_profile","user.user_id","=","user_profile.user_id")->
        join("user_refferal","user.user_id","=","user_refferal.user_id")->
        get(array("user.user_id as userid","user.email","user.first_name as firstname","user.last_name as lastname","user.username","user_profile.profile_picture as profilepicture","user_profile.refferal_code as refferalcode","user_profile.phone_number as phonenumber", "user_refferal.refferal_point as refferalpoint","user_refferal.refferal_users_count as refferalusercount"));
        if (isset($result[0])) {
            return $result[0];
        } else {
            return null;
        }
    }

}
