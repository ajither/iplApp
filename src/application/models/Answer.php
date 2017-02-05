<?php
/**
 * Created by PhpStorm.
 * User: ajith
 * Date: 5/2/17
 * Time: 3:44 PM
 */
namespace models;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Capsule\Manager as Capsule;

class Answer extends Eloquent
{

    public $timestamps = false;
    protected $table = 'match_answer';
    protected $fillable = [

    ];

    public function __construct()
    {
        $this->tableObject = $this->getConnectionResolver()->connection()->table($this->table);
    }

    public function setCurrentMatchAnswer($data)
    {
        return $this->tableObject
            ->updateOrInsert(["user_id" => $data['user_id'], "match" => $data['matchNo']], $data);
    }
}