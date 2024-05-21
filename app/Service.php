<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use App\Order;

class Service extends Model
{
    use SoftDeletes; 

    protected $table = 'services';
    protected $fillable = ['name', 'image', 'initial_price','active','parent_id','text','text_en','name_en','initial_price_excellence_client','number_admin','number_user','numbers','device_number'];
    protected $hidden = ['created_at', 'updated_at', 'image'];
    protected $dates = ['deleted_at'];
    protected $appends = ['image_path'];

    public function getImagePathAttribute()
    {
        if($this->image != null)
        {
            return asset('public/uploads/services/'. $this->image);
        }
        else
        {
            return asset('public/img/default_service.png');
        }
    }

    public function workingHours()
    {
        $service_orders = Order::where('service_id', $this->id)->get();
        $totalDuration = 0;

        foreach($service_orders as $order)
        {
            $startTime = Carbon::parse($order->team_start_at);
            $finishTime = Carbon::parse($order->team_end_at);
            $totalDuration = $totalDuration + $finishTime->diff($startTime)->format('%H');
        }

        return $totalDuration;
    }

    public function serviceTeam()
    {
       return $this->hasMany('App\Team')->withTrashed();
    }

    public function serviceOrder()
    {
       return $this->hasMany('App\Order');
    }
    public function checkSub(){
        $check = Service::where('parent_id',$this->id)->where('active',1)->count();
        return $check;
    }
}
