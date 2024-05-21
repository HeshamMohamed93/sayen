<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class EmergencyServiceReason extends Model
{
    //use SoftDeletes; 

    protected $table = 'emergency_services_reasons';
    protected $fillable = ['reason','reason_en','service_id','status'];
    protected $hidden = ['created_at', 'updated_at'];
    protected $dates = ['deleted_at'];

}
