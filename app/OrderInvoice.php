<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderInvoice extends Model
{
    protected $table = 'orders_invoices';
    protected $fillable = ['order_id', 'coupon_id','initial_price', 'coupon_discount', 'team_receive_money', 'user_accept_added_price',
                            'team_added_price', 'team_added_price_desc', 'final_price', 'pay_by'];
    protected $hidden = ['created_at', 'updated_at'];

     public function orderCoupon()
    {
       return $this->belongsTo('App\Coupon', 'coupon_id')->withTrashed();
    }

    public function order()
    {
       return $this->belongsTo('App\Order', 'order_id');
    }

    public function teamAddedPrice()
    {
        if($this->team_added_price != null)
        {
            return unserialize($this->team_added_price);
        }
        else
        {
            return [];
        }
    }

    public function teamAddedPriceDesc()
    {
        if($this->team_added_price_desc != null)
        {
            return unserialize($this->team_added_price_desc);
        }
        else
        {
            return [];
        }
    }

}