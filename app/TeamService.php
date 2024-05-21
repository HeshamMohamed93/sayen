<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TeamService extends Model
{
    public $timestamps = false;

    public function Service()
    {
       return $this->belongsTo('App\Service', 'service_id','id');
    }
    public function Team()
    {
       return $this->belongsTo('App\Team', 'team_id','id');
    }

}