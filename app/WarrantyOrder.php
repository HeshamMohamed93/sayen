<?php

namespace App;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WarrantyOrder extends Model
{
   use SoftDeletes;
    protected $table = 'warranty_orders';
    protected $fillable = ['user_id', 'order_number','service_id', 'rate_team_value',
                            'rate_team_comment', 'rate_service_value', 'rate_service_comment', 'status', 'cancelled_at', 'cancelled_by', 'cancel_reason'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'] ;
    protected $dates = ['deleted_at'];


    public function refund()
    {
       return $this->belongsTo('App\Refund');
    }

    public function workingHours()
    {
       $startTime = Carbon::parse($this->team_start_at);
       $finishTime = Carbon::parse($this->team_end_at);
       $totalDuration = $finishTime->diff($startTime)->format('%H:%i');
       return $totalDuration;
    }

    public function orderService()
    {
       return $this->belongsTo('App\Service', 'service_id')->withTrashed();
    }
    
    public function orderTeam()
    {
       return $this->belongsTo('App\Team', 'team_id')->withTrashed();
    }

    public function orderUser()
    {
       return $this->belongsTo('App\User', 'user_id')->withTrashed();
    }

    public function orderCancelReason()
    {
       return $this->belongsTo('App\ReportProblem', 'cancel_reason')->withTrashed();
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
      $visit_date = new Carbon($this->visit_date);
      return $visit_date->format('Y-m-d g:i A');
   }
   
}