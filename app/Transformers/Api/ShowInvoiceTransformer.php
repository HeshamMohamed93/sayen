<?php

namespace App\Transformers\Api;

use App\Transformers\BaseTransformer as Transformer;
use Carbon\Carbon;

class ShowInvoiceTransformer extends Transformer
{
    public function getLang()
    {
        $lang = app()->getlocale();
        return $lang;
    }
    public function transform($order) : array
    {
        $lang = $this->getLang();
        if($lang == 'ar'){
            $order_data = [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'service' => $order->orderService->name,
                'date' => date('Y-m-d',strtotime($order->team_start_at)),
                'order_status' => $order->orderStatus()
            ];
        }else{
            $order_data = [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'service' => $order->orderService->name_en,
                'date' => date('Y-m-d',strtotime($order->team_start_at)),
                'order_status' => $order->orderStatus()
          
            ];
        } 
            $order_data += [
                'client' => $order->orderUser->name,
                'client_type' => ($order->orderUser->excellence_client == 1) ? trans('admin.excellence_client'):trans('admin.excellence_client'),
                'building' => ($order->orderUser->building)?$order->orderUser->building->name:'',
                'floor' => $order->floor,
                'flat' => $order->orderUser->flat,
                'floor' => $order->floor,
                'team_name' => isset($order->orderTeam->name) ? $order->orderTeam->name : '',
                'initial_price' => $order->orderInvoice->initial_price,
                'coupon_discount' => $order->orderInvoice->coupon_discount,
                'pay_method' => $order->pay_method,
                'pay_status' => $order->pay_status,
                'final_price' => $order->orderInvoice->final_price,
            ];
        
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