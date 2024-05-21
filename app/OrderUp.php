<?php

namespace App;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class OrderUp extends Model
{
    protected $table = 'order_up';
    protected $fillable = ['user_id', 'order_number', 'visit_date','work_details','hand_work','materials_used','image','hand_work_price','materials_used_price','team_id','flat','building'];
    protected $hidden = ['created_at', 'updated_at'] ;

    
    public function orderTeam()
    {
       return $this->belongsTo('App\Team', 'team_id')->withTrashed();
    }

    public function orderUser()
    {
       return $this->belongsTo('App\User', 'user_id')->withTrashed();
    }


   public function visitDate12HFormat()
   {
      $visit_date = new Carbon($this->visit_date);
      return $visit_date->format('Y-m-d');
   }
   public function visitDate12HFormatTime()
   {
      $visit_date = new Carbon($this->visit_date);
      return $visit_date->format('g:i A');
   }
}