<?php

namespace App\Transformers\Api;

use App\Transformers\BaseTransformer as Transformer;
use Carbon\Carbon;

class UserOrderTransformer extends Transformer
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
                'date' => $this->getDateString($order->visit_date),
                'type' => 'order',
                'order_status' => $order->status
            ];
        }else{
            $order_data = [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'title' => trans('api.maintenance_service').' '.$order->orderService['name_en'],
                'date' => $this->getDateString($order->visit_date),
                'type' => 'order',
                'order_status' => $order->status
          
            ];
        } 
        if(!empty($order->order_id) && $order->warranty == 0 ){
            $order_data['title'] = $order_data['title'].'-'.trans('api.warranty');
            $order_data['type'] = 'warranty';
        }
        if($method == 'show')
        {
            $order_data += [
                'images' => $this->imagesPath($order->images),
                'notes' => $order->notes,
                'order_id' => $order->order_id,
                'initial_price' => $order->orderInvoice->initial_price,
                'coupon_discount' => $order->orderInvoice->coupon_discount,
                'pay_method' => $order->pay_method,
                'pay_status' => $order->pay_status,
                'pay_by' => $order->orderInvoice->pay_by,
                'team_added_price' => ($order->orderInvoice->teamAddedPrice()) ? $order->orderInvoice->teamAddedPrice() : [],
                'team_added_price_desc' => ($order->orderInvoice->teamAddedPriceDesc()) ? $order->orderInvoice->teamAddedPriceDesc() : [],
                'user_accept_added_price' => $order->orderInvoice->user_accept_added_price,
                'total_before_discount' => $order->orderInvoice->initial_price + array_sum($order->orderInvoice->teamAddedPrice()),
                'final_price' => $order->orderInvoice->final_price,
                'team_name' => isset($order->orderTeam->name) ? $order->orderTeam->name : '',
                'excellence_client' => $order->orderUser->excellence_client,
                'team_phone' => isset($order->orderTeam->phone) ? '0'.substr($order->orderTeam->phone, 3) : '',
                'service_rated' => ($order->rate_service_value == '0') ? 0 : $order->rate_service_value,
                'team_image' => "",
                'warranty' => $order->warranty,
                'offer' => ($order->orderOffer) ? $order->orderOffer:null,
                'finish_image' => ($order->finish_image != null) ? asset('public/uploads/orders/'. $order->finish_image) : null,
            ];

            if($order->orderInvoice->coupon_id != null)
            {
                if($order->orderInvoice->orderCoupon->discount_type == 1)
                {
                    $order_data['total_before_team_add_price'] = $order->orderInvoice->initial_price - ($order->orderInvoice->initial_price * $order->orderInvoice->orderCoupon->discount) / 100;
                }
                else if($order->orderInvoice->orderCoupon->discount_type == 2)
                {
                    $order_data['total_before_team_add_price'] = $order->orderInvoice->initial_price - $order->orderInvoice->orderCoupon->discount;
                }

                if($order_data['total_before_team_add_price'] < 0)
                {
                    $order_data['total_before_team_add_price'] = 0;
                }
            }
            else
            {
                $order_data['total_before_team_add_price'] = $order->orderInvoice->initial_price;
            }
            
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
            if($order->warranty != 2 && $order_data['type'] != 'warranty'){
                $order_data['warrantyDate'] = '';
                if(isset($order->orderService) && ($order->orderService->warranty && $order->orderService->warranty != 0)){
                    $endWarranty = date('Y-m-d H:i:s', strtotime($order->visit_date. ' + '.$order->orderService->warranty.' days'));
                    $today = Carbon::now()->format('Y-m-d H:i:s');  
                    if($today <= $endWarranty){
                        $order_data['warranty'] = '1';
                        $order_data['warrantyDate'] = $endWarranty;
                    }else{
                        $order_data['warranty'] = 0;
                    }              
                    
                }else{
                    $order_data['warranty'] = 0;
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