<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReportProblem extends Model
{
    use SoftDeletes; 

    protected $table = 'report_problems';
    protected $fillable = ['problem','problem_en'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
    protected $dates = ['deleted_at'];
}
