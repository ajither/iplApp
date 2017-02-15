<?php
/**
 * Created by PhpStorm.
 * User: ajith
 * Date: 15/2/17
 * Time: 8:52 AM
 */
namespace models;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Capsule\Manager as Capsule;

class MatchSchedule extends Eloquent
{

    public $timestamps = false;
    protected $table = 'ipl_schedule';
    protected $fillable = [

    ];

    public function __construct()
    {
        $this->tableObject = $this->getConnectionResolver()->connection()->table($this->table);
    }

    public function getTeamSchedule()
    {
        return $this->tableObject->get();
    }

}