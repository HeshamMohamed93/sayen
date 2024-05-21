<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use App\Notification;
use DB;

class NotificationController extends BaseController
{
    // === Get user or team notifications ===
    public function getNotifications(Request $request)
    {
        $validator = Validator::make( $request->all(), [
            'user_type' => ['required',Rule::in(['1','2'])],
            'offset' => 'required|integer|min:0',
            'limit' => 'required|integer|min:1'
        ]);
        
        if ($validator->fails()) 
        {
            return $this->respondWithError($validator->messages());
        }
        else
        {
            if($request->user_type == 1) //=== user
            {
                config(['auth.defaults.guard' => 'api-users', 'auth.defaults.passwords' => 'users']);
            }
            else if($request->user_type == 2) //=== team
            {
                config(['auth.defaults.guard' => 'api-teams', 'auth.defaults.passwords' => 'teams']);
            }
            
            $member = $this->getAuthenticatedUser();
            
            if($member)
            {
                $check_user = $this->checkUserStatus($member);
                
                if($this->user_status == true)
                {
                    $notifications = Notification::where([['user_type', $request->user_type], ['user_id', $member->id]])
                                            ->orderBy('seen', 'DESC')
                                            ->orderBy('created_at', 'DESC')
                                            ->offset($request->offset * $request->limit)
                                            ->limit($request->limit)->get();
                                            
                    $count_notifications = Notification::where([['user_type', $request->user_type], ['user_id', $member->id]])->count();
                    $pages = ceil($count_notifications/$request->limit);

                    return $this->respond(['notifications' => $notifications, 'pages' => $pages, 'current_page' => $request->offset+1, 'count_data' => $count_notifications ,'status_code' => 200]);
                }
                else
                {
                    return $check_user;
                }
            }
            else
            {
                return $this->respondWithError(trans('api.user_not_exist'));
            }
        }
    }
    // === End function ===

    // === Set notification to be seen ===
    public function setNotificationToSeen(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'notification_id' => 'required|integer|exists:notifications,id',
            'user_type' => ['required',Rule::in(['1','2'])],
        ]);

        if ($validator->fails()) 
        {
            return $this->respondWithError($validator->messages());
        }

        if($request->user_type == 1) //=== user
        {
            config(['auth.defaults.guard' => 'api-users', 'auth.defaults.passwords' => 'users']);
        }
        else if($request->user_type == 2) //=== team
        {
            config(['auth.defaults.guard' => 'api-teams', 'auth.defaults.passwords' => 'teams']);
        }

        $member = $this->getAuthenticatedUser();

        if($member)
        {
            $check_user = $this->checkUserStatus($member);
            
            if($this->user_status == true)
            {
                $notification = Notification::where([['id', $request->notification_id], ['user_id', $member->id], 
                                                        ['user_type', $request->user_type], ['seen', '0']])->first();     

                if($notification)
                {
                    $image = explode('.', $notification->image);
                    $notification->seen = '1';
                    $notification->image = $image[0].'_gray.'.$image[1];
                    $notification->save();
                    return $this->respond(['message' =>  trans('api.suucess_update') , 'status_code' => 200]);
                }
                else
                {
                    return $this->respondWithError(trans('api.not_found_notification')); 
                }
            }
            else
            {
                return $check_user;
            }
        }
        else
        {
            return $this->respondWithError(trans('api.user_not_exist'));
        }
    }
    // === End function ===
}
