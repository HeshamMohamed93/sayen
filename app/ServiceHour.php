<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ServiceHour extends Model
{

    protected $table = 'service_hours';
    public $timestamps = false;
    public function Service()
    {
        return $this->belongsTo('App\Service', 'service_id','id'); 
    }
}
