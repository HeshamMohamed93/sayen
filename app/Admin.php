<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;


class Admin extends Authenticatable
{
    use Notifiable;
    use SoftDeletes; 

    protected $table = "admins";
    protected $fillable = ['name', 'email', 'password', 'image','show_order_deleted','show_client_deleted'];
    protected $hidden = ['password', 'remember_token'];
    protected $casts = ['email_verified_at' => 'datetime'];
    protected $dates = ['deleted_at'];
    protected $appends = ['image_path'];

    //only the `deleted` event will get logged automatically
    //protected static $recordEvents = ['deleted'];

    

    public function getImagePathAttribute()
    {
        if($this->image != null)
        {
            return asset('public/uploads/admins/'. $this->image);
        }
        else
        {
            return asset('public/img/default_user.png');
        }
    }

    public function hasPermission()
    {
        return $this->hasMany('App\AdminPermission');
    }
}
