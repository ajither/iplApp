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
     * @brief      Fetches user details.
     * @param      $userName  User name.
     * @return     User details
     */
    public function fetchDetailsByUsername($userName) {
        $result = $this->tableObject->
                        where('user_name', $userName)->
                        where('is_active', '1')->get();
        if (isset($result[0])) {
            return $result[0];
        } else {
            return null;
        }
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       July 13, 2016
     * @brief      Fetches all user ids.
     * @return     User ids
     */
    public function fetchAllUserIds() {
        $result = $this->tableObject->
                        where('is_active', '1')->get(array('id'));
        return $result;
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       August 29, 2016
     * @brief      Delete user
     * @param      $userId  User Id.
     * @return     Delete operation output
     */
    public function deleteUser($user_id) {
        return $this->tableObject->delete($user_id);
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       April 26, 2016
     * @brief      Fetches user details.
     * @param      $id  User Id.
     * @return     User details
     */
    public function fetchUserDetails($id) {
        $result = $this->tableObject->
                        where('id', $id)->
                        where('is_active', '1')->get();
        if (isset($result[0])) {
            return $result[0];
        } else {
            return null;
        }
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       August 29, 2016
     * @brief      Update user details.
     * @param      $id  User Id.
     * @return     Update operation output
     */
    public function updateUser($data) {
        return $this->tableObject->
                        where('id', $data['id'])->
                        update($data);
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       April 26, 2016
     * @brief      Fetches user role.
     * @param      $id  User Id.
     * @return     User details
     */
    public function fetchUserRole($userId) {
        $result = $this->tableObject->
                        where('id', $userId)->
                        where('is_active', '1')->get(array('role_id'));
        if (isset($result[0])) {
            return $result[0];
        } else {
            return null;
        }
    }
    
    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       October 31, 2016
     * @brief      Soft Delete user
     * @param      $id 
     * @return     
     */
    public function deleteUserSoft($Id) {
        return $this->tableObject->
                        where('id', $Id)->update(array('is_active' => '0'));
    }

}
