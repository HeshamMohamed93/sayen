<?php

namespace App\Http\Controllers\Api\V2\Team;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\V2\BaseController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Order;
use DB;
use App\Transformers\Api\TeamOrderTransformer;
use App\Transformers\Api\OrderInvoiceTransformer;
use Carbon\Carbon;
use App\Coupon;
use App\User;

class InvoiceController extends BaseController
{
    public function __construct(Request $request)
    {
        $this->middleware('ApiTeam');
    }

    // === Get order invoices for finished orders ===
    public function index(Request $request, TeamOrderTransformer $transformer)
    {
        app()->setLocale($request->header('lang'));
        $validator = Validator::make($request->all(), [
            'offset' => 'required|integer|min:0',
            'limit' => 'required|integer|min:1',
            'date_from' => 'nullable',
            'date_to' => 'nullable',
        ]);
        
        if ($validator->fails()) 
        {
            return $this->respondWithError($validator->messages());
        }
        else
        {
            $orders = (new Order)->newQuery();

            $orders->where('team_id', $request->team['id'])->whereIn('status', ['3', '4']);

            if($request->has('date_from') && $request->has('date_to'))
            {
                $orders->whereRaw('date(visit_date) between "'.arTOen($request->date_from).'" and "'.arTOen($request->date_to).'"');
            }
            else if($request->has('date_from'))
            {
                $orders->whereRaw('date(visit_date) = "'.arTOen($request->date_from).'"');
            }

            $orders->orderBy('created_at', 'DESC');

            $result_orders = $orders->offset($request->offset * $request->limit)->limit($request->limit)->get();
            $count_data = $orders->count();

            return $this->respond(['orders' => $transformer->transformCollection($result_orders, 'invoice'), 'count_data' => $count_data, 'status_code' => 200]); 
        }
    }
    // === End function ===

    // === Add new pricing to existing final total ===
    public function addPricing(Request $request, OrderInvoiceTransformer $transformer)
    {
        app()->setLocale($request->header('lang'));
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|integer|exists:orders,id',
            'price' => 'required|array',
            'price_desc' => 'required|array',
        ]);

        if ($validator->fails()) 
        {
            return $this->respondWithError($validator->messages());
        }

        $order = Order::where([['id', $request->order_id], ['team_id', $request->team['id']]])->whereIN('status', ['6', '5'])->first();
        
        if($order)
        {
            $order->orderInvoice->team_added_price_desc = serialize($request->price_desc);
            $order->orderInvoice->team_added_price = serialize($request->price);
            $request->price = array_sum($request->price);
            $order->orderInvoice->final_price = $request->price;
            $order->orderInvoice->user_accept_added_price = '0';

            if($order->orderInvoice->coupon_id)
            {
                $total_price = $request->price;
                $coupon = Coupon::where('id', $order->orderInvoice->coupon_id)->withTrashed()->first();
                $apply_coupon = $this->calculateCouponValues($coupon->code, $total_price, $order->service_id);
                
                if($apply_coupon['status'] == true)
                {
                    $order->orderInvoice->final_price = $apply_coupon['final_price'];
                    $order->orderInvoice->coupon_discount = $apply_coupon['coupon_discount'];
                }
            }

            $order->orderInvoice->user_accept_added_price = '0';
            $success_save = $order->orderInvoice->save();
            
            if($success_save)
            {
                $user = User::find($order->user_id);
                $notification['order_id'] = $order->id;
                $notification['user_id'] = $order->user_id;
                $notification['user_type'] = '1';
                $notification['message'] = trans('notification.team_update_price_notification', ['value' => $order->order_number],$user->device_lang);
                $notification['image'] = 'edit_price.png';
                createNotification($notification);
                
                return $this->respond(['invoice' => $transformer->transform($order->orderInvoice), 'status_code' => 200]);
            }
            else
            {
                return $this->respondWithError(trans('api.invalid_update'));
            }
        }
        else
        {
            return $this->respondWithError(trans('api.not_found_order'));
        }
    }
    // === End function ===
    
}
