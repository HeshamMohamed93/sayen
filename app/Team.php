<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use DB;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use App\Order;

class Team extends Authenticatable implements JWTSubject
{
    use Notifiable;
    use SoftDeletes;

    protected $table = 'teams';
    protected $fillable = ['name', 'phone', 'email','password', 'service_id', 'active', 'phone_verified', 'image', 'player_id', 'lat', 'lng'];
    protected $hidden = ['image','password', 'created_at', 'updated_at', 'player_id'];
    protected $dates = ['deleted_at'];
    protected $appends = ['image_path'];

    public function getImagePathAttribute()
    {
        if($this->image != null)
        {
            return asset('public/uploads/teams/'. $this->image);
        }
        else
        {
            return asset('public/img/default_user.png');
        }
    }

    public function workingHours()
    {
        $team_orders = Order::where('team_id', $this->id)->get();
        $totalDuration = 0;

        foreach($team_orders as $order)
        {
            $startTime = Carbon::parse($order->team_start_at);
            $finishTime = Carbon::parse($order->team_end_at);
            $totalDuration = $totalDuration + $finishTime->diff($startTime)->format('%H');
        }

        return $totalDuration;
    }

    public function teamStatus()
    {
        if($this->active == 0)
        {
            return ['text' =>trans('admin.not_active'), 'icon' => '<i class="fa fa-unlock m--font-info"></i>', 'to_update_value' => 1,  'tooltip' => trans('admin.unblock_account')];
        }
        else if($this->active == 1)
        {
            return ['text' =>trans('admin.active'), 'icon' => '<i class="fa fa-lock m--font-info"></i>', 'to_update_value' => 0,  'tooltip' => trans('admin.block_account')];
        }
    }

    public function teamService()
    {
       return $this->belongsTo('App\Service', 'service_id')->withTrashed();
    }

    public function teamOrders()
    {
        return $this->hasMany('App\Order', 'team_id', 'id')->count();
    }
    public function teamOrdersWithDate($from,$to)
    {
        if(isset($from) && $to == null)
        {
            return $this->hasMany('App\Order', 'team_id', 'id')->whereDate('visit_date', '>=', $from)->count();
        }
        else if(isset($to) && $from == null)
        {   
            return $this->hasMany('App\Order', 'team_id', 'id')->whereDate('visit_date', '<', $to)->count();
        }
        else if(isset($to) && isset($from))
        {
            return $this->hasMany('App\Order', 'team_id', 'id')->wherebetween('visit_date',[$from,$to])->count();
        }
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }


}
