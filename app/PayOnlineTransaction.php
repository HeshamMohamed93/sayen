<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PayOnlineTransaction extends Model
{
    protected $table = 'pay_online_transactions';
    protected $fillable = ['order_id', 'reference_id', 'company', 'name', 'number', 'pay_amount'];
    protected $hidden = ['created_at', 'updated_at'];

    public function order()
    {
       return $this->belongsTo('App\Order', 'order_id');
    }

}