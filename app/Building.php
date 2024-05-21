<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Building extends Model
{
    use SoftDeletes;
    protected $table = 'buildings';
    protected $fillable = ['name', 'owner_name', 'address', 'notes','discount'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
    protected $dates = ['deleted_at'];

    public function getNameUnits()
    {
       $units = $this->hasMany('App\Unit')->get();
       $unitNames = [];
       foreach($units as $unit){
            array_push($unitNames,(isset(explode('/',$unit->name)[1]))?explode('/',$unit->name)[1]:'');
       }
       sort($unitNames);
       return $unitNames;
    }
}
