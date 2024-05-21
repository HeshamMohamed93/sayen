<?php

namespace App\Transformers\Api;

use App\Transformers\BaseTransformer as Transformer;
use Carbon\Carbon;

class TeamEmergencyOrderTransformer extends Transformer
{
    public function getLang()
    {
        $lang = app()->getlocale();
        return $lang;
    }
    public function transform($order, $method = 'index') : array
    {
        if($this->getLang() == 'ar'){
            $order_data = [
                'id' => $order->id,
                'title' => trans('api.maintenance_service').' '.$order->orderService['name'],
            ];
        }else{
            $order_data = [
                'id' => $order->id,
                'title' => trans('api.maintenance_service').' '.$order->orderService['name_en'],
            ];
        }

        if($method == 'index')
        {
            $order_data += [
                'date' => $this->getDateString($order->created_at). ' ' . $this->getTime($order->created_at),
                'order_number' => $order->order_number,
                'order_status' => $order->status,
            ];
        }
        else if($method == 'show')
        {
            $order_data += [
                'date' => $this->getDateString($order->created_at). ' ' . $this->getTime($order->created_at),
                'order_number' => $order->order_number,
                'order_status' => $order->status,
                'notes' => $order->text,
                'service' => ($order->add_service != null) ? $order->add_service : '',
                'material' => ($order->add_material != null) ? $order->add_material : '',
                'admin_note' => ($order->admin_note == null) ? '' : $order->admin_note,
                'finish_image' => ($order->finish_image != null) ? asset('public/uploads/orders/'. $order->finish_image) : null,
                'user_phone' => '0'.substr($order->orderUser->phone, 3),
                'user_name' => ($order->orderUser->name != null) ? $order->orderUser->name : '0'.substr($order->orderUser->phone, 3),
            ];
        }

        return $order_data;
    }

    private function getTime($dateTime)
    {
        $formate_hour = date('h:i', strtotime($dateTime));

        if(date('A', strtotime($dateTime)) == 'PM')
        {
            $formate_hour .= ' '.trans('api.evening');
        }
        else if(date('A', strtotime($dateTime)) == 'AM')
        {
            $formate_hour .= ' '.trans('api.morning');
        }
        
        return $formate_hour ;
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