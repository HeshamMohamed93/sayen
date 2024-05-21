<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StaticPage extends Model
{
    protected $table = 'static_pages';
    protected $fillable = ['title', 'content', 'facebook', 'twitter', 'instagram', 'whatsapp', 'telegram','lang','phone'];
    protected $hidden = ['created_at', 'updated_at', 'image'];

}
