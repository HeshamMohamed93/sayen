<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $fillable = ['name', 'icon', 'module_order', 'parent_id', 'url_name'];
}
