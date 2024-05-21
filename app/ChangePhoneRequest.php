<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ChangePhoneRequest extends Model
{
    protected $table = 'change_phone_requests';
    protected $fillable = ['user_id', 'user_type', 'phone', 'phone_verified'];
    protected $hidden = ['created_at', 'updated_at'];
}
