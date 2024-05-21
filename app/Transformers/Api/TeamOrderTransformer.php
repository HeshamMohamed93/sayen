<?php

namespace App\Transformers\Api;

use App\Transformers\BaseTransformer as Transformer;
use Carbon\Carbon;

class TeamOrderTransformer extends Transformer
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
                'time' => $this->getTime($order->visit_date),
                'lat' => $order->lat,
                'lng' => $order->lng,
                'user_phone' => '0'.substr($order->orderUser->phone, 3),
            ];
        }

        else if($method == 'invoice')
        {
            $order_data += [
                'date' => $this->getDateString($order->visit_date),
                'order_number' => $order->order_number,
                'order_status' => $order->status,
            ];
        }

        else if($method == 'show')
        {
            $order_data += [
                'lat' => $order->lat,
                'lng' => $order->lng,
                'floor' => $order->floor,
                'address' => $order->address,
                'service' => ($order->add_service != null) ? $order->add_service : '',
                'material' => ($order->add_material != null) ? $order->add_material : '',
                'date' => $this->getDateString($order->visit_date),
                'order_number' => $order->order_number,
                'order_status' => $order->status,
                'images' => $this->imagesPath($order->images),
                'notes' => $order->notes,
                'user_phone' => '0'.substr($order->orderUser->phone, 3),
                'user_name' => ($order->orderUser->name != null) ? $order->orderUser->name : '0'.substr($order->orderUser->phone, 3),
                'pay_method' => $order->pay_method,
                'pay_status' => $order->pay_status,
                'initial_price' => $order->orderInvoice->initial_price,
                'coupon_discount' => $order->orderInvoice->coupon_discount,
                'team_added_price' => ($order->orderInvoice->teamAddedPrice()) ? $order->orderInvoice->teamAddedPrice() : [],
                'team_added_price_desc' =>($order->orderInvoice->teamAddedPriceDesc()) ? $order->orderInvoice->teamAddedPriceDesc() : [],
                'total_before_discount' => $order->orderInvoice->initial_price + array_sum($order->orderInvoice->teamAddedPrice()),
                'final_price' => $order->orderInvoice->final_price,
                'pay_by' => $order->orderInvoice->pay_by,
                'excellence_client' => $order->orderUser->excellence_client,
                'user_accept_added_price' => $order->orderInvoice->user_accept_added_price,
                'team_receive_money' => $order->orderInvoice->team_receive_money,
                'offer' => ($order->orderOffer) ? $order->orderOffer:null,
                'user_image' => ($order->orderUser->image) ? asset('public/uploads/users/'. $order->orderUser->image) : asset('public/img/default_user.png'),
                'finish_image' => $order->finish_image,
                'device_numbers' => $order->device_numbers,
            ];
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