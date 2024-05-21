<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AdminPermission extends Model
{
    protected $table = "admin_permissions";
    protected $fillable = ['admin_id', 'module_id', 'can_create', 'can_edit', 'can_show', 'can_delete'];
    protected $hidden = ['password', 'remember_token'];
}
