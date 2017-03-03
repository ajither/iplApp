<?php
/**
 * @author     Ajith E R, <ajith@salesx.io>
 * @date       December 19, 2016
 * @brief      Sign up API
 */

namespace models;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Capsule\Manager as Capsule;

class User_Total_Point extends Eloquent
{

    public $timestamps = false;
    protected $table = 'user_total_point';
    protected $fillable = [
    ];

    public function __construct()
    {
        $this->tableObject = $this->getConnectionResolver()->connection()->table($this->table);
    }

    public function updateTotalPoint($data)
    {
        return $this->tableObject->updateOrInsert(['user_id' => $data['user_id']], $data);
    }
}