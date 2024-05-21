<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CommonQuestion extends Model
{
    protected $table = 'common_questions';
    protected $fillable = ['question', 'answer'];
    protected $hidden = ['created_at', 'updated_at'];
}
