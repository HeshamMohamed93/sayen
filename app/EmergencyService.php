<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use App\Order;

class EmergencyService extends Model
{
    use SoftDeletes; 

    protected $table = 'emergency_services';
    protected $fillable = ['title','status','title_en'];
    protected $hidden = ['created_at', 'updated_at'];
    protected $dates = ['deleted_at'];

    public function reasons()
    {
        return $this->hasMany('App\EmergencyServiceReason','service_id','id')->where('status',1);
    }
}
