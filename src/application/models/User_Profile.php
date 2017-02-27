<?php
/**
 * Created by PhpStorm.
 * User: ajith
 * Date: 22/12/16
 * Time: 11:25 AM
 */
namespace models;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Capsule\Manager as Capsule;

class User_Profile extends Eloquent
{

    public $timestamps = false;
    protected $table = 'user_profile';
    protected $fillable = [
        'user_id',
        'oauth_provider',
        'oauth_uid',
        'gender',
        'locale',
        'phone_number',
        'profile_picture'
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
    public function addUserProfile($data) {
        return $this->tableObject->insertGetId($data);
    }

    public function editUserProfile($data)
    {
        return $this->tableObject->
        where('user_id', $data['user_id'])->
        update($data);
    }

    public function userPhotoUrl($user_id)
    {
        $fetch = $this->tableObject->
        where('user_id', $user_id)->
        get(array('profile_picture'));
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
}