<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use App\Order;

class Offer extends Model
{
    use SoftDeletes; 

    protected $table = 'offers';
    protected $fillable = ['title','title_en', 'image', 'price','status','text','service_id','date','from','to','show'];
    protected $hidden = ['created_at', 'updated_at', 'image'];
    protected $dates = ['deleted_at'];
    protected $appends = ['image_path'];

    public function getImagePathAttribute()
    {
        if($this->image != null)
        {
            return asset('public/uploads/offers/'. $this->image);
        }
        else
        {
            return asset('public/img/default_offer.png');
        }
    }
    public function Service()
    {
        return $this->hasOne('App\Service', 'id','service_id');
    }
}
