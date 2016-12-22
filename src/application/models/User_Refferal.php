<?php
/**
 * Created by PhpStorm.
 * User: ajith
 * Date: 22/12/16
 * Time: 12:42 PM
 */
namespace models;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Capsule\Manager as Capsule;

class User_Refferal extends Eloquent
{

    public $timestamps = false;
    protected $table = 'user_refferal';
    protected $fillable = [
        'user_id',
        'refferal_code',
        'refferal_point',
        'refferal_users_count'
    ];

    public function __construct()
    {
        $this->tableObject = $this->getConnectionResolver()->connection()->table($this->table);
    }

    /**
     * User: ajith
     * Date: 22/12/16
     * Time: 11:25 AM
     */
    public function addUserRefferal($data) {
        return $this->tableObject->insertGetId($data);
    }

    /**
     * User: ajith
     * Date: 22/12/16
     * Time: 11:25 AM
     */
    public function updateUserRefferal($refferalCode)
    {
        return $this->tableObject->
        where('refferal_code', $refferalCode['refferal_code'])->update($refferalCode);
    }

    /**
     * User: ajith
     * Date: 22/12/16
     * Time: 11:25 AM
     */
    public function checkRefferalCode($refferal_code)
    {
        $result = $this->tableObject->
        where('refferal_code', $refferal_code)->get();
        if (isset($result[0])) {
            return $result[0];
        } else {
            return null;
        }
    }

}