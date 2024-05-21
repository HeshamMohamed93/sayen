<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VerifyOTP extends Model
{
    protected $table = 'verify_otp';
    protected $fillable = ['user_id', 'code', 'verified', 'user_type', 'code_type', 'expired_at', 'sent_times'];
    protected $hidden = ['created_at', 'updated_at'];
}