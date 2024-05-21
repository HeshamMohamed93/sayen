<?php

namespace App\Http\Controllers\Api\V2\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\V2\BaseController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Order;
use App\Service;
use Carbon\Carbon;
use DB;
use App\Transformers\Api\UserOrderTransformer;
use App\User;
use App\OrderInvoice;
use App\Notification;
use App\PayOnlineTransaction;
use App\ServiceHour;
use Carbon\CarbonPeriod;
use App\Team;

class OrderController extends BaseController
{
    public function __construct(Request $request)
    {
        $this->middleware('ApiUser')->except('successPay', 'redirectAfterPay', 'payForm');
    }
    
    // === Get current and previous orders ===
    public function index(Request $request, UserOrderTransformer $transformer)
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
        
        $order = (new Order)->newQuery();
        $order->where('user_id', $request->user['id']);

        if($request->order_type == 'current')
        {
            $order->WhereIn('status', ['5', '1', '2','6']);
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
    
    public function checkDate(Request $request){
        $today = Carbon::now()->format('Y-m-d');
        $today30Days = Carbon::now()->addDays(30)->format('Y-m-d');
        
        $period = CarbonPeriod::create($today, $today30Days);
        $dates = [];
        foreach($period as $key => $date){
            array_push($dates,$date->format('Y-m-d'));
        }
        
        foreach($dates as $key => $date){
            $hours = $this->checkTimeDate2($request->service_id,$date);
            $array = ['date'=> $date, 'time'=>$hours];
            array_push($dates,json_decode(json_encode($array), true));
        }
        $dates = array_slice($dates,31);
        return $this->respond(['dates'=> $dates]);
    }

    // === check Time Date new order ===
    public function checkTimeDate2($service,$date){
        $todayTime = Carbon::now()->format('H');         
        if($date > Carbon::now()->format('Y-m-d')){
            $getTimaAvailableService = ServiceHour::where('service_id',$service)->where('count','>',0)->pluck('count','hour')->toArray();
        }elseif($date < Carbon::now()->format('Y-m-d')){
            return $this->respondWithError(trans('api.old_date_time'));
        }elseif($date == Carbon::now()->format('Y-m-d')){
            $display2Hour = [$todayTime,$todayTime + 1]; 
            $getTimaAvailableService = ServiceHour::where('service_id',$service)->where('count','>',0)->where('hour','>=',$todayTime)->whereNotIn('hour',$display2Hour)->pluck('count','hour')->toArray();
        }
        
        $getTimaAvailable = [];
        foreach($getTimaAvailableService as $hour => $hourCount){
            $newDate = $date .' '.$hour.':00';
            $visit_date = Carbon::parse(arTOen($newDate))->format('Y-m-d H');
            $checkOrderHour = Order::where('service_id',$service)->where('visit_date',$visit_date)->count();
            if($checkOrderHour < $hourCount){
                array_push($getTimaAvailable,"$hour".':00');
            }
        }
        return response()->json($getTimaAvailable)->getData();
    }
    // === End function ===
    // === check Time Date new order ===
    public function checkTimeDate(Request $request){
        $todayTime = Carbon::now()->format('H'); 
        $date = Carbon::parse(arTOen($request->date))->format('Y-m-d H:i');
        
        if($request->date > Carbon::now()->format('Y-m-d')){
            $getTimaAvailableService = ServiceHour::where('service_id',$request->service_id)->where('count','>',0)->pluck('count','hour')->toArray();
        }elseif($request->date < Carbon::now()->format('Y-m-d')){
            return $this->respondWithError(trans('api.old_date_time'));
        }elseif($request->date == Carbon::now()->format('Y-m-d')){
            $display2Hour = [$todayTime,$todayTime + 1]; 
            $getTimaAvailableService = ServiceHour::where('service_id',$request->service_id)->where('count','>',0)->where('hour','>=',$todayTime)->whereNotIn('hour',$display2Hour)->pluck('count','hour')->toArray();
        }
        
        $getTimaAvailable = [];
        foreach($getTimaAvailableService as $hour => $hourCount){
            $newDate = $request->date .' '.$hour.':00';
            $visit_date = Carbon::parse(arTOen($newDate))->format('Y-m-d H');
            $checkOrderHour = Order::where('service_id',$request->service_id)->where('visit_date',$visit_date)->count();
            if($checkOrderHour < $hourCount){
                array_push($getTimaAvailable,"$hour".':00');
            }
        }
        return $this->respond(['hours'=> $getTimaAvailable]);
    }
    // === End function ===
    // === Create new order ===
    public function store(Request $request)
    {
        //return $this->respond(['message' => trans('api.success_order_creation'), 'status_code' => 200, 'request' => $request->all()]);
        $ids = [1,2,36];
        $numbers = array('06','02');  

        // $lastOrder = Order::select('order_number')                
        // ->Where(function ($query) use($numbers) {
        //      for ($i = 0; $i < count($numbers); $i++){
        //         $query->orwhere('order_number', 'like', $numbers[$i] .'%');
        //      }      
        // })->orderBy('created_at','DESC')->first();

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
        $service = Service::find($request->service_id);
        
        $number = "$service->number_user".date('y').date('m').date('d').$newNUmber;
        
        app()->setLocale($request->header('lang'));
        $validator = Validator::make($request->all(), [
            'service_id' => 'required|integer|exists:services,id',
            'images' => 'required|min:1|max:5',
            'notes' => 'required',
            'lat' => 'required',
            'lng' => 'required',
            'address' => 'required',
            'floor' => 'required|max:50',
            'visit_date' => 'required',
            'coupon_code' => 'nullable|size:4',
            'pay_method' => ['required',Rule::in(['1','2'])],
            // 'pay_type' => ['required',Rule::in(['cash','apple','cards'])],
        ]);
            
        if ($validator->fails()) 
        {
            return $this->respondWithError($validator->messages());
        }
        else
        {
            $today = Carbon::now()->format('Y-m-d H:i');  
            $visit_date = Carbon::parse(arTOen($request->visit_date))->format('Y-m-d H:i');            
            $checkServiceHour = ServiceHour::where('service_id',$request->service_id)->where('hour',Carbon::parse(arTOen($request->visit_date))->format('H'))->first();
            $checkServiceHour = isset($checkServiceHour)? $checkServiceHour->count:0;
            $checkOrderHour = Order::where('service_id',$request->service_id)->where('visit_date',$visit_date)->count();
            $dateAfter2Hour = Carbon::now()->addHours(2)->format('Y-m-d H:i'); 
           
            $dateToday= date('Y-m-d',strtotime($today));
            $dateVisit = date('Y-m-d',strtotime($visit_date));

            
            // if($dateVisit >= $dateToday && $dateAfter2Hour > $visit_date){
            //     return $this->respondWithError(trans('api.2hour_date_time'));
            // }
            //dd($dateVisit,$visit_date,$dateVisit >= $dateToday,$dateAfter2Hour > $visit_date);
            if($visit_date < $today)
            {
                return $this->respondWithError(trans('api.old_date_time'));
            }
            // if($checkServiceHour == 0 || $checkOrderHour >= $checkServiceHour )
            // {
            //     return $this->respondWithError(trans('api.complete_service_orders'));
            // }
            $order_data = $request->except('images');
            $order_data['visit_date'] = arTOen($visit_date);
            $order_data['user_id'] = $request->user['id'];
            $order_data['order_number'] = $number;
            $order_data['status'] = '1';
            $order_data['offer_id'] = $request->offer_id;
            $order_data['initial_price'] = (isset($request->price_after_coupon)) ? $request->price_after_coupon : $request->initial_price;
            $order_data['pay_status'] = '0';
            $order_data['device'] = $request->device_type;
            $order_data['device_numbers'] = (isset($request->numbers))?$request->numbers:0;
            $image = '';
            
            if($request['images'])    
            {
                foreach($request['images'] as $order_image)
                {
                    $image .= uploadImage($order_image, 'orders').',';
                }
            }
            $order_data['images'] = rtrim($image, ',');
            
            DB::beginTransaction();

            try 
            {
                $user = User::findOrFail($request->user['id']);
                $user_data = $request->only('lat','lng','address','floor');
                $user = $user->update($user_data);
                $saved_order = Order::create($order_data);
                $order_data['id'] = $saved_order['id'];
                
                $saved_invoice = $this->createInvoice($order_data);
                
                if($saved_invoice['status'] == false)
                {
                    throw new \Exception($saved_invoice['message']);
                }
                else
                {
                    $saved_order->pay_status = $saved_invoice['pay_status'];
                    $saved_order->save();

                    // === admin notification ===
                    $notification['order_id'] = $saved_order->id;
                    $notification['user_type'] = '3';
                    $notification['image'] = 'default_service.png';
                    $notification['message'] = trans('notification.admin_new_order', ['value' => $saved_order->order_number]);    

                    Notification::create($notification);

                    DB::commit(); 

                    if($order_data['pay_method'] == 2 && $order_data['pay_type'] == 'cards')
                    {
                        $saved_order->deleted_at = now();
                        $saved_order->pay_type = 'visa';
                        $saved_order->save();
                        $order_id = $saved_order->id;
                        $deviceType = ($request->device_type)?$request->device_type:'ios';
                        return $this->respond(['url' => route('pay-form', [$order_id,$deviceType]), 'order_id' => $saved_order->id]);
                    }elseif($order_data['pay_method'] == 2 && $order_data['pay_type'] == 'apple')
                    {
                        $saved_order->pay_type = 'apple';
                        $saved_order->save();
                        return $this->respond(['order_id' => $saved_order->id]);
                    }
                    else
                    {
                        return $this->respond(['message' => trans('api.success_order_creation'), 'status_code' => 200, 'order_id' => $saved_order->id]);
                    }
                }
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

        $invoice_data['initial_price'] = $invoice_data['final_price'] = $order['initial_price'];
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
    public function show($id, Request $request, UserOrderTransformer $transformer)
    {
        app()->setLocale($request->header('lang'));
        $order = Order::where([['id', $id], ['user_id', $request->user['id']]])->first();
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
        if($request->type == 'order'){
            $order = Order::where([['id', $request->order_id], ['user_id', $request->user['id']], ['status', '3']])->first();
        }elseif($request->type == 'EmergencyOrder'){
            $order = EmergencyOrder::where([['id', $request->order_id], ['user_id', $request->user['id']], ['status', '3']])->first();
        }
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
            'code' => 'required',
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

        if($invoice && ($invoice->order->status == 6 || $invoice->order->status == 5))
        {
            $message;
            $team = Team::find($invoice->order->team_id);
            if($request->status == 1)   //=== accept
            {
                $invoice->user_accept_added_price = '1';
                $invoice->save();
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
                $invoice->save();
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
                        'company' =>(isset($payment->source['company']))?$payment->source['company']:'',
                        'name' => (isset($payment->source['name']))?$payment->source['name']:'',
                        'number' => (isset($payment->source['number']))?$payment->source['number']:'',
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