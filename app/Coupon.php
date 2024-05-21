<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coupon extends Model
{
    use SoftDeletes; 

    protected $table = 'coupons';
    protected $fillable = ['code', 'expired_at', 'discount_type', 'discount', 'num_of_users', 'num_of_usage_per_user', 'used_times', 'status', 'active', 'service_id','date_from','date_to'];
    protected $hidden = ['created_at', 'updated_at'];
    protected $dates = ['deleted_at'];

    public function discountValue()
    {
        if($this->discount_type == 1)
        {
            return $this->discount.' %';
        }
        else if($this->discount_type == 2)
        {
            return $this->discount.' '.trans('admin.currency');
        }
    }

    public function usedTimes()
    {
        return $this->hasMany('App\OrderInvoice');//->count();
    }

    public function couponService()
    {
        return $this->belongsTo('App\Service', 'service_id')->withTrashed(); 
    }

    public function couponStatus()
    {
        if($this->active == 0)
        {
            return ['text' =>trans('admin.not_active'), 'icon' => '<i class="fa fa-unlock m--font-info"></i>', 'to_update_value' => 1, 'tooltip' => trans('admin.unblock_account')];
        }
        else if($this->active == 1)
        {
            return ['text' =>trans('admin.active'), 'icon' => '<i class="fa fa-lock m--font-info"></i>', 'to_update_value' => 0, 'tooltip' => trans('admin.block_account')];
        }
    }
}