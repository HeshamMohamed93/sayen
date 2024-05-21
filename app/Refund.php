<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    protected $table = 'refunds';
    protected $fillable = ['order_id', 'reference_id','refund_amount'];
    
    public function order()
    {
        $this->belongsTo('App\Order', 'order_id');
    }

}
