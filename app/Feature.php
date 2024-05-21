<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Feature extends Model
{
    protected $table = 'features';
    protected $fillable = ['image', 'title', 'content'];
    protected $hidden = ['created_at', 'updated_at'];
    protected $appends = ['image_path'];

    public function getImagePathAttribute()
    {
        if($this->image != null)
        {
            return asset('public/uploads/features/'. $this->image);
        }
        else
        {
            return asset('public/img/default_feature.png');
        }
    }

}
