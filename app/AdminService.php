<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AdminService extends Model
{
    public $timestamps = false;

    public function Service()
    {
       return $this->belongsTo('App\Service', 'service_id','id');
    }
    public function Admin()
    {
       return $this->belongsTo('App\Admin', 'admin_id','id');
    }

}