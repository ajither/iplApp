<?php

/**
 * @author     Nikhil N R, <nikhil@salesx.io>
 * @date       April 24, 2016
 * @brief      Encryption_Keymap Model
 */

namespace models;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Capsule\Manager as Capsule;

class Encryption_Keymap extends Eloquent {

    public $timestamps = false;
    protected $table = 'Encryption_Keymap';
    protected $fillable = [
        'user_id',
        'encryption_key'
    ];

    public function __construct() {
        $this->tableObject = $this->getConnectionResolver()->connection()->table($this->table);
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       April 26, 2016
     * @brief      Add Encryption keymap.
     * @param      $data  Data array.
     * @return     Insert operation output
     */
    public function addEncryptionKeymap($data) {
        return $this->tableObject->updateOrInsert(["user_id" => $data['user_id']], $data);
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       April 27, 2016
     * @brief      fetch user id.
     * @param      $encKey  Encryption key.
     * @return     User Id
     */
    public function fetchUidWithEncryptionKeymap($encKey) {
        $result = $this->tableObject->
                where('encryption_key', $encKey)->
                value('user_id');
        if ($result == '') {
            return 0;
        } else {
            return $result;
        }
    }

    /**
     * @author     Ajith E R, <ajith@salesx.io>
     * @date       December 22, 2016
     * @brief      Login Actions
     */
    public function fetchEncryptionKeymapWithUid($userId) {
        $result = $this->tableObject->
                where('user_id', $userId)->
                value('encryption_key');
        if ($result == '') {
            return NULL;
        } else {
            return $result;
        }
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       July 22, 2016
     * @brief      Delete Encryption Key map
     * @param      $userId  User Id
     * @return     Result of the delete operation
     */
    public function deleteKeymap($userId) {
        $result = $this->tableObject->
                where('user_id', $userId)->
                delete();
        return $result;
    }

}
