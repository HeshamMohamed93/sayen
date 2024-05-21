<?php

namespace App\Transformers\Api;

use App\Transformers\BaseTransformer as Transformer;
use Carbon\Carbon;

class UserEmergencyOrderTransformer extends Transformer
{
    public function getLang()
    {
        $lang = app()->getlocale();
        return $lang;
    }
    public function transform($order, $method = 'index') : array
    {
        $lang = $this->getLang();
        if($lang == 'ar'){
            $order_data = [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'title' => trans('api.maintenance_service').' '.$order->orderService['name'],
                'type' => 'Emergency',
                'date' => $this->getDateString($order->created_at),
                'order_status' => $order->status
            ];
        }else{
            $order_data = [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'title' => trans('api.maintenance_service').' '.$order->orderService['name'],
                'type' => 'Emergency',
                'date' => $this->getDateString($order->created_at),
                'order_status' => $order->status
            ];
        }

        if($method == 'show')
        {
            $order_data += [
                'notes' => $order->text,
                'team_name' => isset($order->orderTeam->name) ? $order->orderTeam->name : '',
                'team_phone' => isset($order->orderTeam->phone) ? '0'.substr($order->orderTeam->phone, 3) : '',
                'team_image' => "",
                'finish_image' => $order->finish_image,
            ];

            
            
            if(isset($order->orderTeam))
            {
                if($order->orderTeam->image != null)
                {
                    $order_data['team_image'] = asset('public/uploads/teams/'. $order->orderTeam->image);
                }
                else
                {
                    $order_data['team_image'] = asset('public/img/default_user.png');
                }
            }
        }
        return $order_data;
    }

    private function imagesPath($images)
    {
        $images = explode(',', $images);
        $images_full_path = [];

        foreach($images as $image)
        {
            $images_full_path[] = asset('public/uploads/orders/'. $image);
        }

        return $images_full_path;
    }

    private function getDateString($date)
    {
        $lang = $this->getLang();
        if($lang == 'ar'){
            $months = ["Jan" => "يناير", "Feb" => "فبراير", "Mar" => "مارس", "Apr" => "أبريل", "May" => "مايو", "Jun" => "يونيو", 
                    "Jul" => "يوليو", "Aug" => "أغسطس", "Sep" => "سبتمبر", "Oct" => "أكتوبر", "Nov" => "نوفمبر", "Dec" => "ديسمبر"];
        }else{
            $months = ["Jan" => "January", "Feb" => "February", "Mar" => "March ", "Apr" => "April", "May" => "May", "Jun" => "June", 
                    "Jul" => "June", "Aug" => "August", "Sep" => "September", "Oct" => "October", "Nov" => "November", "Dec" => "December"];
        }
        $formate_data = date('d', strtotime($date)).' ';
        $formate_data .= $months[date('M', strtotime($date))];

        if(date('A', strtotime($date)) == 'PM')
        {
            $formate_data .= ', '.trans('api.evening_period'); 
        }
        else if(date('A', strtotime($date)) == 'AM')
        {
            $formate_data .= ', '.trans('api.morning_period');
        }
        
        return $formate_data ;
    }

}