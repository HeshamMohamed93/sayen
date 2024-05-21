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
use App\Notification;
use App\EmergencyOrder;
use App\Transformers\Api\TeamEmergencyOrderTransformer;
use App\Setting;
use App\Service;
use PDF;
use App\User;

class OrderController extends BaseController
{
    public function __construct(Request $request)
    {
        $this->middleware('ApiTeam');
    }
    
    // === Get orders in specific date ===
    public function index(Request $request, TeamOrderTransformer $transformer)
    {
        app()->setLocale($request->header('lang'));
        $validator = Validator::make($request->all(), [
            'date' => 'required',
            'offset' => 'required|integer|min:0',
            'limit' => 'required|integer|min:1'
        ]);
        
        if ($validator->fails()) 
        {
            return $this->respondWithError($validator->messages());
        }
        else
        {
            $orders = Order::where([['team_id', $request->team['id']], ['visit_date', 'like', '%'.arTOen($request->date).'%']])
                        ->whereIn('status', ['2', '5','6'])
                        ->orderBy('created_at', 'DESC')->offset($request->offset * $request->limit)->limit($request->limit)->get();

            $count_data = Order::where([['team_id', $request->team['id']], ['visit_date', 'like', '%'.arTOen($request->date).'%']])
                            ->whereIn('status', ['2', '5','6'])->count();
            $settings = Setting::select('team_app_android_version','team_app_ios_version')->first();
            return $this->respond(['orders' => $transformer->transformCollection($orders),'count_data' => $count_data,'status_code' => 200,'settings'=>$settings]);     
        }
    }
    // === End function ===

    // === Get order detail ===
    public function show($id, Request $request, TeamOrderTransformer $transformer)
    {
        $order = Order::where([['id', $id], ['team_id', $request->team['id']]])->first();
        app()->setLocale($request->header('lang'));
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

    // === Go work ===
    public function goWork(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|integer|exists:orders,id',
        ]);
        
        if ($validator->fails()) 
        {
            return $this->respondWithError($validator->messages());
        }

        // $current_order = Order::where([['team_id', $request->team['id']], ['status', '2']])->first();

        // if($current_order)
        // {
        //     return $this->respondWithError(trans('api.not_close_order'));
        // }

        $order = Order::where([['id', $request->order_id], ['team_id', $request->team['id']], ['status', '5']])->first();

        if($order)
        {
            if($order->orderInvoice->team_added_price != null && $order->orderInvoice->user_accept_added_price == 0)
            {
                return $this->respondWithError(trans('api.still_not_accect_or_refuse'));
            }

            if(Carbon::parse($order->visit_date)->toDateString() > Carbon::today()->toDateString())
            {
                return $this->respondWithError(trans('api.cant_start_today'));
            }

            $order->team_start_at = Carbon::now();
            $order->status = '6';
            $success_save = $order->save();
            
            if($success_save)
            {
                $user = User::find($order->user_id);
                $notification['order_id'] = $order->id;
                $notification['user_id'] = $order->user_id;
                $notification['user_type'] = '1';
                $notification['message'] = trans('notification.team_go_work_service_notification',[],$user->device_lang);
                $notification['image'] = 'default_service.png';

                createNotification($notification);
                
                return $this->respond(['message' => trans('api.success_start_working'), 'status_code' => 200]);
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

    // === Start work ===
    public function startWork(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|integer|exists:orders,id',
        ]);
        
        if ($validator->fails()) 
        {
            return $this->respondWithError($validator->messages());
        }

        // $current_order = Order::where([['team_id', $request->team['id']], ['status', '2']])->first();

        // if($current_order)
        // {
        //     return $this->respondWithError(trans('api.not_close_order'));
        // }

        $order = Order::where([['id', $request->order_id], ['team_id', $request->team['id']], ['status', '6']])->first();

        if($order)
        {
            if($order->orderInvoice->team_added_price != null && $order->orderInvoice->user_accept_added_price == 0)
            {
                return $this->respondWithError(trans('api.still_not_accect_or_refuse'));
            }

            if(Carbon::parse($order->visit_date)->toDateString() > Carbon::today()->toDateString())
            {
                return $this->respondWithError(trans('api.cant_start_today'));
            }

            $order->team_start_at = Carbon::now();
            $order->status = '2';
            $success_save = $order->save();
            
            if($success_save)
            {
                $user = User::find($order->user_id);
                $notification['order_id'] = $order->id;
                $notification['user_id'] = $order->user_id;
                $notification['user_type'] = '1';
                $notification['message'] = trans('notification.team_start_service_notification',[],$user->device_lang);
                $notification['image'] = 'default_service.png';

                createNotification($notification);
                
                return $this->respond(['message' => trans('api.success_start_working'), 'status_code' => 200]);
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

    // === End work ===
    public function endWork(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|integer|exists:orders,id',
        ]);
        
        if ($validator->fails()) 
        {
            return $this->respondWithError($validator->messages());
        }

        $order = Order::where([['id', $request->order_id], ['team_id', $request->team['id']], ['status', '2']])->first();

        if($order)
        {
            $order->team_end_at = Carbon::now();
            $order->save();
            $user = User::find($order->user_id);
            $notification['order_id'] = $order->id;
            $notification['user_id'] = $order->user_id;
            $notification['user_type'] = '1';
            $notification['message'] = trans('notification.team_finish_work',[],$user->device_lang);
            $notification['image'] = 'default_service.png';
            
            createNotification($notification);

            return $this->respond(['message' => trans('api.success_end_working'), 'status_code' => 200]);
        }
        else 
        {
            return $this->respondWithError(trans('api.not_found_order'));
        }
    }
    // === End function ===

    // === Report for problem ===
    public function reportProblem(Request $request)
    {
        app()->setLocale($request->header('lang'));
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|integer|exists:orders,id',
            'problem_type' => ['required',Rule::in(['1','2', '3', '4'])],
        ]);

        if ($validator->fails()) 
        {
            return $this->respondWithError($validator->messages());
        }

        $order = Order::where([['id', $request->order_id], ['team_id', $request->team['id']], 
                                ['visit_date', 'like', '%'.Carbon::today()->toDateString().'%']])
                        ->whereNotIn('status', ['3', '4'])->first();

        if($order)
        {
            $order->status = '4';
            $order->cancelled_by = '2';
            $order->cancel_reason = $request->problem_type;
            $order->cancelled_at = Carbon::now();

            if($order->team_start_at != null)
            {
                $order->team_end_at = Carbon::now();
            }

            $success_save = $order->save();

            if($success_save)
            {
                $user = User::find($order->user_id);
                $notification['order_id'] = $order->id;
                $notification['user_id'] = $order->user_id;
                $notification['user_type'] = '1';
                $notification['message'] = trans('notification.team_cancel_service_notification',[],$user->device_lang);
                $notification['image'] = 'default_service.png';
                
                createNotification($notification);

                return $this->respond(['message' => trans('api.success_report'), 'status_code' => 200]);
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

    // === Finish work and confirm receive money ===
    public function finishWork(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|integer|exists:orders,id',
            'pay_by'=> ['nullable',Rule::in(['1','2'])],    // 1 = client, 2 = owner 
        ]);

        if ($validator->fails()) 
        {
            return $this->respondWithError($validator->messages());
        }

        $order = Order::where([['id', $request->order_id], ['team_id', $request->team['id']], ['status', '2']])->first();

        if($order)
        {
            // if($order->pay_method == '1')   //=== cache
            // {
            //     $order->pay_status = '1';
            //     $order->orderInvoice->team_receive_money = '1';
            // }
            $order->pay_status = '1';
            $order->orderInvoice->team_receive_money = '1';
            $order->team_end_at = Carbon::now();
            $order->orderInvoice->pay_by = (string) $request->pay_by;
            $order->orderInvoice->save();
            
            $image = '';
            if($request->finish_work_image)    
            {
                foreach($request->finish_work_image as $order_image)
                {
                    $image .= uploadImage($order_image, 'orders').',';
                }
            }
            $order->finish_image = rtrim($image, ',');
            $order->status = '3';
            
            $order->save();
            $user = User::find($order->user_id);
            // === user notification ===
            $notification['order_id'] = $order->id;
            $notification['user_id'] = $order->user_id;
            $notification['user_type'] = '1';
            $notification['message'] = trans('notification.user_rate_order', ['value' => $order->order_number],$user->device_lang);
            $notification['image'] = 'default_rate.png';

            createNotification($notification);

            // === admin notification ===
            $admin_notification['order_id'] = $order->id;
            $admin_notification['user_type'] = '3';
            $admin_notification['image'] = 'default_service.png';
            $admin_notification['message'] = trans('notification.team_finish_work', ['value' => $order->order_number]);    

            Notification::create($admin_notification);
            $this->sendSms(trans('api.complete_order'), $order->orderUser->phone);
            return $this->respond(['message' => trans('api.success_finish_work'), 'status_code' => 200]);
        }
        else
        {
            return $this->respondWithError(trans('api.not_found_order'));   
        }
    }
    // === End function ===

    // === Add Service  ===
    public function addService(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|integer|exists:orders,id',
            'service'=> 'required',
        ]);

        if ($validator->fails()) 
        {
            return $this->respondWithError($validator->messages());
        }

        $order = Order::where('id', $request->order_id)->first();

        if($order)
        {
            $order->add_service = $request->service;
            $order->save();
            return $this->respond(['message' => trans('api.success_add_service'), 'status_code' => 200]);
        }
        else
        {
            return $this->respondWithError(trans('api.not_found_order'));   
        }
    }
    // === End function ===

    // === Add Material  ===
    public function addMaterial(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|integer|exists:orders,id',
            'material'=> 'required',
        ]);

        if ($validator->fails()) 
        {
            return $this->respondWithError($validator->messages());
        }

        $order = Order::where('id', $request->order_id)->first();

        if($order)
        {
            $order->add_material = $request->material;
            $order->save();
            return $this->respond(['message' => trans('api.success_add_material'), 'status_code' => 200]);
        }
        else
        {
            return $this->respondWithError(trans('api.not_found_order'));   
        }
    }
    // === End function ===
    
    // === Finish work and confirm receive money ===
    public function emergencyOrder(Request $request, TeamEmergencyOrderTransformer $transformer){
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
            $orders = (new EmergencyOrder)->newQuery();

            $orders->where('team_id', $request->team['id']) ;
            if($request->has('date_from') && $request->has('date_to'))
            {
                $orders->whereRaw('date(created_at) between "'.arTOen($request->date_from).'" and "'.arTOen($request->date_to).'"');
            }
            else if($request->has('date_from'))
            {
                $orders->whereRaw('date(created_at) = "'.arTOen($request->date_from).'"');
            }
            $orders->orderBy('created_at', 'DESC');

            $result_orders = $orders->offset($request->offset * $request->limit)->limit($request->limit)->get();
            $count_data = $orders->count();
            return $this->respond(['orders' => $transformer->transformCollection($result_orders), 'count_data' => $count_data, 'status_code' => 200]); 
        }
    }

    // === Get Emergency order detail ===
    public function showEmergencyOrder($id, Request $request, TeamEmergencyOrderTransformer $transformer)
    {
        $order = EmergencyOrder::where([['id', $id], ['team_id', $request->team['id']]])->first();
        app()->setLocale($request->header('lang'));
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
}
