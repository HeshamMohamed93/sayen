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

class EmergencyOrderController extends BaseController
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
                       
                        ->orderBy('created_at', 'DESC')->offset($request->offset * $request->limit)->limit($request->limit)->get();

            $count_data = Order::where([['team_id', $request->team['id']], ['visit_date', 'like', '%'.arTOen($request->date).'%']])
                            ->count();
            return $this->respond(['orders' => $transformer->transformCollection($orders),'count_data' => $count_data,'status_code' => 200]);     
        }
    }
    // === End function ===

    // === Go work ===
    public function goWork(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|integer|exists:emergency_orders,id',
        ]);
        
        if ($validator->fails()) 
        {
            return $this->respondWithError($validator->messages());
        }

        // $current_order = EmergencyOrder::where([['team_id', $request->team['id']], ['status', '2']])->first();

        // if($current_order)
        // {
        //     return $this->respondWithError(trans('api.not_close_order'));
        // }

        $order = EmergencyOrder::where([['id', $request->order_id], ['team_id', $request->team['id']], ['status', '5']])->first();

        if($order)
        {
            if(Carbon::parse($order->visit_date)->toDateString() > Carbon::today()->toDateString())
            {
                return $this->respondWithError(trans('api.cant_start_today'));
            }

            $order->team_start_at = Carbon::now();
            $order->status = '6';
            $success_save = $order->save();
            
            if($success_save)
            {
                $notification['order_id'] = $order->id;
                $notification['user_id'] = $order->user_id;
                $notification['user_type'] = '1';
                $notification['message'] = trans('notification.team_go_work_service_notification');
                $notification['image'] = 'default_service.png';

               // createNotification($notification);
                
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
            'order_id' => 'required|integer|exists:emergency_orders,id',
        ]);
        
        if ($validator->fails()) 
        {
            return $this->respondWithError($validator->messages());
        }

        // $current_order = EmergencyOrder::where([['team_id', $request->team['id']], ['status', '2']])->first();

        // if($current_order)
        // {
        //     return $this->respondWithError(trans('api.not_close_order'));
        // }

        $order = EmergencyOrder::where([['id', $request->order_id], ['team_id', $request->team['id']], ['status', '6']])->first();

        if($order)
        {
            if(Carbon::parse($order->visit_date)->toDateString() > Carbon::today()->toDateString())
            {
                return $this->respondWithError(trans('api.cant_start_today'));
            }

            $order->team_start_at = Carbon::now();
            $order->status = '2';
            $success_save = $order->save();
            
            if($success_save)
            {
                $notification['order_id'] = $order->id;
                $notification['user_id'] = $order->user_id;
                $notification['user_type'] = '1';
                $notification['message'] = trans('notification.team_start_service_notification');
                $notification['image'] = 'default_service.png';

                //createNotification($notification);
                
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
            'order_id' => 'required|integer|exists:emergency_orders,id',
        ]);
        
        if ($validator->fails()) 
        {
            return $this->respondWithError($validator->messages());
        }

        $order = EmergencyOrder::where([['id', $request->order_id], ['team_id', $request->team['id']], ['status', '2']])->first();

        if($order)
        {
            $order->team_end_at = Carbon::now();
            $order->save();
            
            $notification['order_id'] = $order->id;
            $notification['user_id'] = $order->user_id;
            $notification['user_type'] = '1';
            $notification['message'] = trans('notification.team_finish_work');
            $notification['image'] = 'default_service.png';
            
            //createNotification($notification);

            return $this->respond(['message' => trans('api.success_end_working'), 'status_code' => 200]);
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
            'order_id' => 'required|integer|exists:emergency_orders,id'
        ]);

        if ($validator->fails()) 
        {
            return $this->respondWithError($validator->messages());
        }

        $order = EmergencyOrder::where([['id', $request->order_id], ['team_id', $request->team['id']], ['status', '2']])->first();
        if($order)
        {
            $image = '';
            if($request->finish_work_image)    
            {
                foreach($request->finish_work_image as $order_image)
                {
                    $image .= uploadImage($order_image, 'orders').',';
                }
            }
            $order->finish_image = rtrim($image, ',');
            $order->team_end_at = Carbon::now();
            $order->status = '3';
            $order->save();

            // === user notification ===
            $notification['order_id'] = $order->id;
            $notification['user_id'] = $order->user_id;
            $notification['user_type'] = '1';
            $notification['message'] = trans('notification.user_rate_order', ['value' => $order->order_number]);
            $notification['image'] = 'default_rate.png';

            //createNotification($notification);

            // === admin notification ===
            $admin_notification['order_id'] = $order->id;
            $admin_notification['user_type'] = '3';
            $admin_notification['image'] = 'default_service.png';
            $admin_notification['message'] = trans('notification.team_finish_work', ['value' => $order->order_number]);    

            //Notification::create($admin_notification);
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
            'order_id' => 'required|integer|exists:emergency_orders,id',
            'service'=> 'required',
        ]);

        if ($validator->fails()) 
        {
            return $this->respondWithError($validator->messages());
        }

        $order = EmergencyOrder::where('id', $request->order_id)->first();

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
            'order_id' => 'required|integer|exists:emergency_orders,id',
            'material'=> 'required',
        ]);

        if ($validator->fails()) 
        {
            return $this->respondWithError($validator->messages());
        }

        $order = EmergencyOrder::where('id', $request->order_id)->first();

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
}