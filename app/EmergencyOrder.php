<?php

namespace App;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class EmergencyOrder extends Model
{
   use SoftDeletes;
   use LogsActivity;
    protected $table = 'emergency_orders';
    protected $fillable = ['device','user_id', 'order_number','team_id', 'status', 'text','service_id','finish_image', 'team_start_at', 'team_end_at', 'rate_team_value','rate_team_comment', 'rate_service_value', 'rate_service_comment', 'cancelled_at', 'cancelled_by', 'cancel_reason','emergency_orders','seen','date'];
    protected $hidden = ['updated_at', 'deleted_at'] ;
    protected $dates = ['deleted_at'];
    protected static $logAttributes = ['admin_note','device','user_id', 'order_number','team_id', 'status', 'text','service_id','finish_image', 'team_start_at', 'team_end_at', 'rate_team_value','rate_team_comment', 'rate_service_value', 'rate_service_comment', 'cancelled_at', 'cancelled_by', 'cancel_reason','emergency_orders','seen','date','created_at'];
   
   public function orderFinishImages()
   {
        $images = explode(',', $this->finish_image);
        $images_full_path = [];

        foreach($images as $image)
        {
            $images_full_path[] = asset('public/uploads/orders/'. $image);
        }

        return $images_full_path;
   }
    public function orderTeam()
    {
       return $this->belongsTo('App\Team', 'team_id')->withTrashed();
    }

    public function orderUser()
    {
       return $this->belongsTo('App\User', 'user_id')->withTrashed();
    }
    public function orderService()
    {
       return $this->belongsTo('App\EmergencyService', 'service_id','id')->withTrashed();
    }

    public function orderStatus()
    {
       switch ($this->status) 
       {
          case 1:
            return trans('admin.new_order');
         break;
         
         case 2:
            return trans('admin.current_order');
         break;
         
         case 3:
            return trans('admin.done_order');
         break;

         case 4:
            if($this->cancelled_by == 1)
            {
               return trans('admin.cancel_order').' - '.trans('admin.cancelled_by', ['value' => trans('admin.client')]);
            }
            else if($this->cancelled_by == 2)
            {
               return trans('admin.cancel_order').' - '.trans('admin.cancelled_by', ['value' => trans('admin.team')]);
            }
            else if($this->cancelled_by == 3)
            {
               return trans('admin.cancel_order').' - '.trans('admin.cancelled_by', ['value' => trans('admin.admin')]);
            }
         break;

         case 5:
            return trans('admin.assign_order_to_team');
         break;
      }
   }
   public function visitDate12HFormat()
   {
      $visit_date = new Carbon($this->created_at);
      return $visit_date->format('Y-m-d g:i A');
   }
   public function orderCancelReason()
    {
       return $this->belongsTo('App\ReportProblem', 'cancel_reason')->withTrashed();
    }
    public function workingHours()
    {
       $startTime = Carbon::parse($this->team_start_at);
       $finishTime = Carbon::parse($this->team_end_at);
       $totalDuration = $finishTime->diff($startTime)->format('%H:%I:%S');
       return $totalDuration;
    }
}