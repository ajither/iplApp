<?php
/**
 * @author     Ajith E R, <ajith@salesx.io>
 * @date       December 19, 2016
 * @brief      Sign up API
 */

namespace models;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Capsule\Manager as Capsule;

class RechargeModel extends Eloquent
{

    public $timestamps = false;
    protected $table = 'user_recharge_request';
    protected $fillable = [
    ];

    public function __construct()
    {
        $this->tableObject = $this->getConnectionResolver()->connection()->table($this->table);
    }

    public function updateRequest($data)
    {
        return $this->tableObject->where('user_id',$data['user_id'])->insert($data);
    }

    public function getRechargeStatus($user_id)
    {
        return $this->tableObject->where("user_id",$user_id)->where("status","Processing")->get();
    }
}