<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';
    protected $fillable = ['order_id', 'user_id', 'user_type', 'message', 'image', 'seen'];
    protected $hidden = ['updated_at', 'image'];
    protected $appends = ['image_path'];

    public function getImagePathAttribute()
    {
        return asset('public/img/'. $this->image);
    }

}
