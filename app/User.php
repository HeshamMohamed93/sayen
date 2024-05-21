<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use DB;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;
    use SoftDeletes; 

    protected $table = 'users';
    protected $fillable = ['name', 'last_name', 'phone', 'password', 'image', 'is_client', 'phone_verified', 'player_id', 'building_id', 'flat', 
                            'email', 'client_type', 'excellence_client', 'active','lat','lng','address','floor','excellence_client_verified'];
    protected $hidden = ['image','password', 'created_at', 'updated_at', 'player_id'];
    protected $dates = ['deleted_at'];
    protected $appends = ['image_path'];

    public function getImagePathAttribute()
    {
        if($this->image != null)
        {
            return asset('public/uploads/users/'. $this->image);
        }
        else
        {
            return asset('public/img/default_user.png');
        }
    }

    public function userExcellence()
    {
        if($this->excellence_client == 1)
        {
            return trans('admin.yes');
        }
        else if($this->excellence_client == 2)
        {
            return trans('admin.no');
        }
    }

    public function userType()
    {
        if($this->client_type == 1)
        {
            return trans('admin.individual');
        }
        else if($this->client_type == 2)
        {
            return trans('admin.company');
        }
    }

    public function userVerifyPhone()
    {
        if($this->phone_verified == 0)
        {
            return trans('admin.not_verify');
        }
        else if($this->phone_verified == 1)
        {
            return trans('admin.verify');
        }
    }

    public function userStatus()
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

    public function userOrders()
    {
        return $this->hasMany('App\Order')->count();
    }

    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = bcrypt($password);
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function building()
    {
        return $this->belongsTo('App\Building', 'building_id')->withTrashed();
    }


}
