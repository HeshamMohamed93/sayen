<?php

namespace App\Http\Controllers\Api\V2\Team;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\ReportProblem;
use App\Http\Controllers\Api\V2\BaseController;
use App\Order;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use App\User;

class ProblemController extends BaseController
{
    public function __construct(Request $request)
    {
        $this->middleware('ApiTeam');
    }

    // === Get types of problem === 
    public function problemTypes(Request $request)
    {
        app()->setlocale($request->header('lang'));
        
        if(app()->getlocale() == 'ar'){
            $types = ReportProblem::select('id','problem')->get();
        }else{
            $types = ReportProblem::select('id','problem_en')->get();
        }
        return $this->respond(['types' => $types,'status_code' => 200]);     
    }
    // === End function ===

    // === Report for problem ===
    public function reportProblem(Request $request)
    {
        app()->setLocale($request->header('lang'));
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'type_id' => 'required|exists:report_problems,id',
        ]);

        if ($validator->fails()) 
        {
            return $this->respondWithError($validator->messages());
        }

        $order = Order::where([['id', $request->order_id], ['team_id', $request->team['id']], 
                                ['visit_date', 'like', '%'.Carbon::today()->toDateString().'%']])
                            ->whereNotIn('status', ['1', '3', '4'])->first();

        if($order)
        {
            $order->status = '4';
            $order->cancelled_by = '2';
            $order->cancel_reason = $request->type_id;
            $order->cancelled_at = Carbon::now();

            if($order->team_start_at != null)
            {
                $order->team_end_at = Carbon::now();
            }

            $success_save = $order->save();
            $user = User::find($order->user_id);
            if($success_save)
            {
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

}
