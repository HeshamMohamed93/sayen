<?php

namespace App;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class Order extends Model
{
   use SoftDeletes;
   use LogsActivity;

    protected $table = 'orders';
    protected $fillable = ['pay_type','device','user_id', 'order_number','service_id', 'images', 'notes', 'offer_id', 'lat', 'lng', 'visit_date', 'rate_team_value', 'alert_before_visit',
                            'rate_team_comment', 'rate_service_value', 'rate_service_comment', 'address', 'floor', 'status', 'pay_method', 'online_pay_transaction_id',
                            'pay_status', 'team_start_at', 'team_end_at', 'cancelled_at', 'cancelled_by', 'cancel_reason','warranty','finish_image','initial_price','device_numbers','seen'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'] ;
    protected $dates = ['deleted_at'];

   protected static $logAttributes = ['pay_type','device','user_id', 'order_number','service_id', 'images', 'notes', 'offer_id', 'lat', 'lng', 'visit_date', 'rate_team_value', 'alert_before_visit',
   'rate_team_comment', 'rate_service_value', 'rate_service_comment', 'address', 'floor', 'status', 'pay_method', 'online_pay_transaction_id',
   'pay_status', 'team_start_at', 'team_end_at', 'cancelled_at', 'cancelled_by', 'cancel_reason','warranty','finish_image','initial_price','device_numbers','seen','team_id','hand_works'];
   //protected static $recordEvents = ['deleted'];

    public function refund()
    {
       return $this->belongsTo('App\Refund');
    }

   public function orderImages()
   {
        $images = explode(',', $this->images);
        $images_full_path = [];

        foreach($images as $image)
        {
            $images_full_path[] = asset('public/uploads/orders/'. $image);
        }

        return $images_full_path;
   }

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

    public function orderPayMethod()
    {
       if($this->pay_method == 1)
       {
          return trans('admin.cache');
       }
       else if($this->pay_method == 2)
       {
         if($this->pay_type){
            return trans('admin.online').'('. trans("admin.$this->pay_type") .')';
         }else{
            return trans('admin.online');
         }
       }
    }

    public function orderPayStatus()
    {
       if($this->pay_status == 0)
       {
          return trans('admin.not_paid');
       }
       else if($this->pay_status == 1)
       {
          return trans('admin.paid');
       }
    }

    public function workingHours()
    {
       $startTime = Carbon::parse($this->team_start_at);
       $finishTime = Carbon::parse($this->team_end_at);
       $totalDuration = $finishTime->diff($startTime)->format('%H:%I:%S');
       return $totalDuration;
    }

    public function orderService()
    {
       return $this->belongsTo('App\Service', 'service_id')->withTrashed();
    }
    public function orderOffer()
    {
       return $this->belongsTo('App\Offer', 'offer_id')->withTrashed();
    }

    public function orderInvoice()
    {
       return $this->hasOne('App\OrderInvoice', 'order_id');
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
         case 6:
            return trans('admin.go_work');
         break;
      }
   }

   public function visitDate12HFormat()
   {
      $visit_date = new Carbon($this->visit_date);
      return $visit_date->format('Y-m-d');
   }
   public function visitDate12HFormatTime()
   {
      $visit_date = new Carbon($this->visit_date);
      return $visit_date->format('g:i A');
   }
   public function teamStartDate12HFormat()
   {
      if($this->team_start_at != null){
         $team_start_date = new Carbon($this->team_start_at);
         return $team_start_date->format('Y-m-d g:i A');
      }
   }
   public function teamEndDate12HFormat()
   {
      if($this->team_end_at != null){
         $team_end_date = new Carbon($this->team_end_at);
         return $team_end_date->format('Y-m-d g:i A');
      }
   }
   
   public function orderTransaction()
   {
      return $this->hasOne('App\PayOnlineTransaction');
   }

    
}