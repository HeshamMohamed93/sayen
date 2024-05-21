<?php

namespace App\Transformers\Api;

use App\Transformers\BaseTransformer as Transformer;

class OrderInvoiceTransformer extends Transformer
{
    public function transform($invoic) : array
    {
        return [
            "id" => $invoic->id,
            "order_id" => $invoic->order_id,
            "initial_price" => $invoic->initial_price,
            "coupon_discount" => $invoic->coupon_discount,
            'team_added_price' => ($invoic->teamAddedPrice()) ? $invoic->teamAddedPrice() : [],
            'team_added_price_desc' => ($invoic->teamAddedPriceDesc())  ? $invoic->teamAddedPriceDesc() : [],
            'total_before_discount' => $invoic->initial_price + array_sum($invoic->teamAddedPrice()),
            "final_price" =>  $invoic->final_price,
            "team_receive_money" =>  $invoic->team_receive_money,
            "user_accept_added_price" => $invoic->user_accept_added_price,
        ];
    }
}