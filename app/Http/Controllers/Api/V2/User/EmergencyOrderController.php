<?php

namespace App\Http\Controllers\Api\V2\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\V2\BaseController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\EmergencyOrder;
use App\Service;
use Carbon\Carbon;
use DB;
use App\Transformers\Api\UserEmergencyOrderTransformer;
use App\User;
use App\OrderInvoice;
use App\Notification;
use App\PayOnlineTransaction;
use App\ServiceHour;
use Mail;
use App\Admin;
use App\Team;
use App\EmergencyService;

class EmergencyOrderController extends BaseController
{
    public function __construct(Request $request)
    {
        $this->middleware('ApiUser')->except('successPay', 'redirectAfterPay', 'payForm');
    }
    
    // === Get current and previous orders ===
    public function index(Request $request, UserEmergencyOrderTransformer $transformer)
    {
        //dd('dddd');
        app()->setLocale($request->header('lang'));
        $validator = Validator::make($request->all(), [
            'offset' => 'required|integer|min:0',
            'limit' => 'required|integer|min:1'
        ]);
        
        if ($validator->fails()) 
        {
            return $this->respondWithError($validator->messages());
        }
        $order = (new EmergencyOrder)->newQuery();
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
            'note' => 'required'
            // 'pay_type' => ['required',Rule::in(['cash','apple','cards'])],
        ]);
            
        if ($validator->fails()) 
        {
            return $this->respondWithError($validator->messages());
        }
        else
        {

            $ids = [1,2];
            $numbers = array('06');  

            // $lastOrder = EmergencyOrder::select('order_number')                
            // ->Where(function ($query) use($numbers) {
            //     for ($i = 0; $i < count($numbers); $i++){
            //         $query->orwhere('order_number', 'like', $numbers[$i] .'%');
            //     }      
            // })->orderBy('created_at','DESC')->first();
            $lastOrder = EmergencyOrder::orderBy('created_at','DESC')->first();
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
            $number = "06".date('y').date('m').date('d').$newNUmber;
            
            $today = Carbon::now()->format('Y-m-d H:i');  
            
            $order_data['user_id'] = $request->user['id'];
            $order_data['text'] = $request->note;
            $order_data['service_id'] = $request->service_id;
            $order_data['order_number'] = $number;
            $order_data['status'] = '1';
            $order_data['device'] = $request->device_type;
            //$order_data['date'] = date('Y-m-d H:i:s');
            DB::beginTransaction();
            try 
            {
                $user = User::findOrFail($request->user['id']);
                $saved_order = EmergencyOrder::create($order_data);
                $order_data['id'] = $saved_order['id'];
                $saved_order->save();

                DB::commit(); 
                $data = ['note' => $order_data['text'],'user' => $user->name,'phone' => $user->phone,'service' => $saved_order->orderService['title'],'orderNumber' => $saved_order->order_number,'floor' =>$saved_order->orderUser->floor , 'flat' =>$saved_order->orderUser->flat ,'building' => ($saved_order->orderUser->building)?$saved_order->orderUser->building->name:''];
                $adminsEmails = ['yaqoub@alamaltamayouz.com','othman.m@alamaltamayouz.com','ali.housseny@alamaltamayouz.com','Mdsarfaraz@alamaltamayouz.com','alshushan.k@alamaltamayouz.com','alrajhi.m@alamaltamayouz.com'];
                Mail::send('Api.emails.EmergencyOrder',$data , function ($message) use ($adminsEmails) {
                    $message->to($adminsEmails)->subject('طلب طواري جديد');
                });
                    
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

    // === Get order detail ===
    public function show($id, Request $request, UserEmergencyOrderTransformer $transformer)
    {
        app()->setLocale($request->header('lang'));
        $order = EmergencyOrder::where([['id', $id], ['user_id', $request->user['id']]])->first();
        if($order)
        {
            return $this->respond(['order' => $transformer->transform($order, 'show'), 'status_code' => 200]);
        }
        else
        {
            return $this->respondWithError(trans('api.not_found_order'));
        }
    }
    // === End function ===

    // === Cancel order ===
    public function cancelOrder(Request $request)
    {
        app()->setLocale($request->header('lang'));
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|integer|exists:orders,id',
            'problem_type' => ['nullable',Rule::in(['2'])],
        ]);

        if ($validator->fails()) 
        {
            return $this->respondWithError($validator->messages());
        }

        $order = Order::where([['id', $request->order_id], ['user_id', $request->user['id']]])->first();
        $message = trans('api.success_cancel_order');

        if($order)
        {
            if($order->status != 1 && $order->status != 5)
            {
                return $this->respondWithError(trans('api.cannot_cancel_order'));
            }

            if($order->pay_method == '2')   //=== online pay
            {
                $ordered_at = Carbon::parse($order->created_at);
                $difference_hours = $ordered_at->diffInHours(Carbon::now());
                
                if($difference_hours < 24) //=== Refund
                {
                    $message = trans('api.success_cancel_order_and_retrieve_amount');
                }
                else
                {
                    $message = trans('api.success_cancel_order_cannot_retrieve_amount');
                }
            }

            $order->status = '4';
            $order->cancelled_by = '1';
            $order->cancelled_at = Carbon::now();
            $order->save();

            return $this->respond(['message' => $message, 'status_code' => 200]);
        }
        else
        {
            return $this->respondWithError(trans('api.not_found_order'));
        }
    }
    // === End function ===

    // === Rate done order ===
    public function rateOrder(Request $request)
    {
        app()->setLocale($request->header('lang'));
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|integer|exists:orders,id',
            'rate_service_value' => 'required|integer|min:0|max:5',
            'rate_service_comment' => 'nullable',
            'rate_team_value' => 'required|integer|min:0|max:5',
            'rate_team_comment' => 'nullable',
        ]);

        if ($validator->fails()) 
        {
            return $this->respondWithError($validator->messages());
        }

        $order = Order::where([['id', $request->order_id], ['user_id', $request->user['id']], ['status', '3']])->first();
        //dd($order,$request->all());
        if($order)
        {
            $order->rate_service_value = $request->rate_service_value;
            $order->rate_service_comment = $request->rate_service_comment;
            $order->rate_team_value = $request->rate_team_value;
            $order->rate_team_comment = $request->rate_team_comment;    
            $order->save();

            return $this->respond(['message' => trans('api.success_rate'), 'status_code' => 200]);
        }
        else
        {
            return $this->respondWithError(trans('api.not_found_order'));
        }
    }
    // === End function ===

    // === Validate used coupon ===
    public function validateCoupon(Request $request)
    {
        app()->setLocale($request->header('lang'));
        $validator = Validator::make($request->all(), [
            'code' => 'required|size:4',
            'total_price' => 'required|numeric|min:0',
            'service_id' => 'required',
        ]);

        if ($validator->fails()) 
        {
            return $this->respondWithError($validator->messages());
        }

        $existing_coupon = $this->checkCoupon(arTOen($request->code), $request->user['id']);

        if($this->coupon_status == true)
        {
            if($existing_coupon->service_id != 0 && $existing_coupon->service_id != $request->service_id)
            {
                return $this->respondWithError(trans('api.invalid_coupon_with_service'));
            }

            $coupon_discount = 0;
            $total_price_after_coupon = $total_price_before_coupon = $request->total_price;

            if($existing_coupon->discount_type == 1)    //=== percentage
            {
                $total_price_after_coupon -= ($request->total_price * $existing_coupon->discount) / 100;
            }
            else    //=== price
            {
                $total_price_after_coupon = $request->total_price - $existing_coupon->discount;

                if($total_price_after_coupon < 0)
                {
                    $total_price_after_coupon = 0;
                }
            }

            $coupon_discount = $request->total_price - $total_price_after_coupon;
            
            return $this->respond(['total_price_before_coupon' => $request->total_price,'total_price_after_coupon' => $total_price_after_coupon, 'coupon_discount' => $coupon_discount, 'status_code' => 200]);
        }
        else
        {
            return  $existing_coupon;
        }
    }
    // === End function ===

    // === Accept or refuse added price via team ===
    public function acceptAddedPrice(Request $request)
    {
        app()->setLocale($request->header('lang'));
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|integer|exists:orders,id',
            'status' => ['required',Rule::in(['1','2'])],   //=== 1 accept, 2 refuse
        ]);

        if ($validator->fails()) 
        {
            return $this->respondWithError($validator->messages());
        }

        $invoice = OrderInvoice::where('order_id', $request->order_id)->first();
        
        if($invoice && $invoice->order->status == 5)
        {
            $message;
            $team = Team::find($invoice->order->team_id);
            if($request->status == 1)   //=== accept
            {
                $invoice->user_accept_added_price = '1';
                $message = trans('api.user_accept_added_price');
                
                $notification['order_id'] = $request->order_id;
                $notification['user_id'] = $invoice->order->team_id;
                $notification['user_type'] = '2';
                $notification['message'] = trans('notification.user_accept_added_price', ['value' => $invoice->order->order_number],$team->device_lang);
                $notification['image'] = 'edit_price.png';

                createNotification($notification);
            }
            else if($request->status == 2)   //=== refuse
            {
                $invoice->user_accept_added_price = '2';
                $invoice->order->status = '4';
                $invoice->order->cancelled_by = '1';
                $invoice->order->cancelled_at = Carbon::now();
                $invoice->order->cancel_reason = '2';

                $invoice->order->save();
                $message = trans('api.user_refuse_added_price');

                $notification['order_id'] = $request->order_id;
                $notification['user_id'] = $invoice->order->team_id;
                $notification['user_type'] = '2';
                $notification['message'] = trans('notification.user_refuse_added_price', ['value' => $invoice->order->order_number],$team->device_lang);
                $notification['image'] = 'edit_price.png';
                
                createNotification($notification);
            }
            
            $invoice->save();

            return $this->respond(['message' =>  $message , 'status_code' => 200]);
        }
        else
        {
            return $this->respondWithError(trans('api.not_found_invoice'));
        }
    }
    // === End function ===

    // === Success online payment ===
    public function successPay(Request $request)
    {
        $existing_order = Order::withTrashed()->find($request->order_id);
        if($existing_order)
        {
            if($request->status == 'paid')
            {
                $paymentService = new \Moyasar\Providers\PaymentService();
                $payment = $paymentService->fetch($request->id);
                if(isset($request->device_type) && $request->device_type == 'android'){
                    $save_data = PayOnlineTransaction::create([
                        'order_id' => $request->order_id,
                        'reference_id' => $payment->id,
                        'company' =>$payment->source->company,
                        'name' => $payment->source->name,
                        'number' => $payment->source->number,
                        'pay_amount' => $payment->amount/100,
                    ]);
                }else{
                    $save_data = PayOnlineTransaction::create([
                        'order_id' => $request->order_id,
                        'reference_id' => $payment->id,
                        'company' =>$payment->source['company'],
                        'name' => $payment->source['name'],
                        'number' => $payment->source['number'],
                        'pay_amount' => $payment->amount/100,
                    ]);
                }
                
                
                if($save_data)
                {
                    $existing_order->online_pay_transaction_id = $save_data->id;
                    $existing_order->deleted_at = null;
                    $existing_order->pay_status = '1';
                    $existing_order->save();
                    $message = 'Success pay';
                    if(isset($request->device_type) && $request->device_type == 'android'){
                        return redirect()->route('redirect-pay',['order_id'=> $request->order_id, 'message' => $message]);
                    }else{
                        return $this->respond(['status' => '1','order_id' => $request->order_id,'message' => $message]);
                    }
                    //
                }
                else
                {
                    Order::where('id', $request->order_id)->delete();
                    $message = 'Invalid pay';
                    if(isset($request->device_type) && $request->device_type == 'android'){
                        return redirect()->route('redirect-pay',['order_id'=> 'null', 'message' => $message]);
                    }else{
                        return $this->respond(['status' => '0','order_id' => 'null','message' => $message]);
                    }
                }
            }
            else
            {
                Order::where('id', $request->order_id)->delete();
                $message = 'Invalid pay';
                if(isset($request->device_type) && $request->device_type == 'android'){
                    return redirect()->route('redirect-pay',['order_id'=> 'null', 'message' => $message]);
                }else{
                    return $this->respond(['status' => '0','order_id' => 'null','message' => $message]);
                }
            }
        }
        else
        {
            $message = 'Invalid pay';
            if(isset($request->device_type) && $request->device_type == 'android'){
                return redirect()->route('redirect-pay',['order_id'=> 'null', 'message' => $message]);
            }else{
                return $this->respond(['status' => '0','order_id' => 'null','message' => $message]);
            }
            
            //
        }
    }
    // === End function ===

    public function redirectAfterPay(Request $request)
    {
        $message = $request->message;
        return view('online_pay.success_pay', compact('message'));
    }

    public function payForm($order_id,$device_type)
    {
        $existing_order = Order::withTrashed()->find($order_id);

        if($existing_order)
        {
            $already_paid = PayOnlineTransaction::where('order_id', $order_id)->first();

            if($already_paid)
            {
                $message = 'Already paid';
                return redirect()->route('redirect-pay',['order_id'=> 'null', 'message' => $message]);
            }
            else
            {
                $total_price = $existing_order->orderInvoice->final_price*100;
                return view('online_pay.form', compact('total_price', 'order_id','device_type'));    
            }
        }
        else
        {
            $message = 'Invalid pay';
            return redirect()->route('redirect-pay',['order_id'=> 'null', 'message' => $message]);
        }
    }

}