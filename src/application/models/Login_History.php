<?php

/**
 * @author     Nikhil N R, <nikhil@salesx.io>
 * @date       April 24, 2016
 * @brief      Login_History Model
 */

namespace models;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Capsule\Manager as Capsule;

class Login_History extends Eloquent {

    public $timestamps = false;
    protected $table = 'Login_History';
    protected $fillable = [
        'user_id',
        'ip'
    ];

    public function __construct() {
        $this->tableObject = $this->getConnectionResolver()->connection()->table($this->table);
    }

    /**
     * @author     Nikhil N R, <nikhil@salesx.io>
     * @date       April 26, 2016
     * @brief      Add login history.
     * @param      $data  Data array.
     * @return     Insert operation output
     */
    public function addLoginHistory($data) {
        $this->tableObject->insert($data);
    }

}
