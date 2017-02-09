<?php
/**
 * Created by PhpStorm.
 * User: ajith
 * Date: 7/2/17
 * Time: 9:01 AM
 */
namespace models;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Capsule\Manager as Capsule;

class Match_Point extends Eloquent
{

    public $timestamps = false;
    protected $table = 'user_match_point';
    protected $fillable = [
    ];

    public function __construct()
    {
        $this->tableObject = $this->getConnectionResolver()->connection()->table($this->table);
    }

    public function fetchTopProfileDetails()
    {
        return $this->tableObject->orderBy('matchpoint','desc')->take(5)->get();
    }

    public function insertMatchpoint($matchPoint)
    {
        return $this->tableObject->insertGetId($matchPoint);
    }

    public function getPoint($user_id)
    {
        return $this->tableObject->where("user_id",$user_id)->value('matchpoint');
    }

    public function updateMatchpoint($matchPoint)
    {
        return $this->tableObject->where("user_id",$matchPoint['user_id'])->update($matchPoint);
    }
}