<?php

namespace App\Http\Controllers\Api\V2\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\V2\BaseController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\WarrantyOrder;
use App\Order;
use App\Service;
use Carbon\Carbon;
use DB;
use App\Transformers\Api\UserWarrantyOrderTransformer;
use App\User;
use App\OrderInvoice;
use App\Notification;
use App\PayOnlineTransaction;
use App\ServiceHour;
use Mail;

class WarrantyOrderController extends BaseController
{
    public function __construct(Request $request)
    {
        $this->middleware('ApiUser')->except('successPay', 'redirectAfterPay', 'payForm');
    }
    
    // === Get current and previous orders ===
    public function index(Request $request, UserWarrantyOrderTransformer $transformer)
    {
        app()->setLocale($request->header('lang'));
        $validator = Validator::make($request->all(), [
            'offset' => 'required|integer|min:0',
            'limit' => 'required|integer|min:1'
        ]);
        
        if ($validator->fails()) 
        {
            return $this->respondWithError($validator->messages());
        }
        $order = (new WarrantyOrder)->newQuery();
        $order->where('user_id', $request->user['id']);

        if($request->order_type == 'current')
        {
            $order->WhereIn('status', ['5', '1', '2']);
        }
        else if($request->order_type == 'previous')
        {
            $order->whereIn('status', ['3', '4']);
        }
        else
        {
            return $this->respondWithError(trans('api.invalid_order_type'));
        }
        $orders = $order->orderBy('created_at', 'DESC')->offset($request->offset * $request->limit)->limit($request->limit)->get();
        $count_data =  $order->count();
        return $this->respond(['orders' => $transformer->transformCollection($orders),'count_data' => $count_data,'status_code' => 200]); 
    }
    // === End function ===

    // === Create new order ===
    public function store(Request $request)
    {
        //return $this->respond(['message' => trans('api.success_order_creation'), 'status_code' => 200, 'request' => $request->all()]);
        app()->setLocale($request->header('lang'));
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|integer|exists:orders,id'
            // 'pay_type' => ['required',Rule::in(['cash','apple','cards'])],
        ]);
            
        if ($validator->fails()) 
        {
            return $this->respondWithError($validator->messages());
        }
        else
        {
            try 
            {
                $old_order = Order::findOrFail($request->order_id);
                $old_order->warranty = '2';
                $old_order->save();
/*
                $ids = [1,2];
                $checkService = Service::where('id',$old_order->service_id)->whereIN('parent_id',$ids)->first();        
                $numbers = array('06','02');  
                $lastOrder = Order::orderBy('created_at','DESC')->first();
                if($lastOrder){
                    $newNUmber = (int)substr($lastOrder->order_number, -4) + 1;
                    if(strlen($newNUmber) == 1){
                        $newNUmber = '000'.$newNUmber;
                    }elseif(strlen($newNUmber) == 2){
                        $newNUmber = '00'.$newNUmber;
                    }else{
                        $newNUmber = '0'.$newNUmber;
                    }
                }else{
                    $newNUmber = '0001';
                }
                if(in_array($old_order->service_id,$ids) || $checkService){
                    $number = "06".date('y').date('m').date('d').$newNUmber;
                }elseif($request->service_id == 25){
                    $number = "02".date('y').date('m').date('d').$newNUmber;
                }
                */
                $saved_order = $old_order->replicate();
                $saved_order->order_id = $request->order_id; // the new order_id
                $saved_order->status = '1';
                $saved_order->order_number = $old_order->order_number;
                $saved_order->team_id = null;
                $saved_order->pay_status = '0';
                $saved_order->online_pay_transaction_id = null;
                $saved_order->warranty = '0';
                $saved_order->save();

                $old_order = Order::findOrFail($request->order_id);
                $old_order->order_id = $saved_order->id;
                $old_order->save();

                $saved_invoice = $this->createInvoice($saved_order);
                DB::commit();
                    
                return $this->respond(['message' => trans('api.success_order_creation'), 'status_code' => 200, 'order_id' => $saved_order->id]);
            } 
            catch (\Exception $e)
            {
                DB::rollback();
                return $this->respondWithError($e->getMessage());
            }
        }
    }
    // === End function ===

    // === Create invoice for new order ===
    private function createInvoice($order)
    {
        $service_price = Service::find($order['service_id']);

        $invoice_data['initial_price'] = $invoice_data['final_price'] = $service_price->initial_price;
        $invoice_data['order_id'] = $order['id'];

        if(isset($order['coupon_code']))
        {
            $apply_coupon = $this->calculateCouponValues($order['coupon_code'], $invoice_data['initial_price'], $order['service_id'], true);

            if($apply_coupon['status'] == true) //coupon applied
            {
                $invoice_data['coupon_id'] =  $apply_coupon['coupon_id'];
                $invoice_data['final_price'] =  $apply_coupon['final_price'];
                $invoice_data['coupon_discount'] =  $apply_coupon['coupon_discount'];
            }
            else    //coupon not applied
            {
                return ['status' => false, 'message' => $apply_coupon['message']]; 
            }
        }

        $invoice_data['user_accept_added_price'] = '1';

        $save_invoice = OrderInvoice::create($invoice_data);

        if($save_invoice)
        {
            return ['status' => true, 'pay_status' => $order['pay_status']];   
        }
        else
        {
            return ['status' => true, 'message' => trans('api.not_saved')];   
        }
    } 
    // === End function ===


}