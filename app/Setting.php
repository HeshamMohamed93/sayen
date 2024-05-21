<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'settings';
    protected $fillable = ['text_emergency','text_emergency_en','user_app_android_url', 'user_app_ios_url', 'team_app_android_url','team_app_ios_url', 'about_sayen_shortcut','user_app_android_version', 'user_app_ios_version', 'team_app_android_version','team_app_ios_version'];
    protected $hidden = ['created_at', 'updated_at'];
}
