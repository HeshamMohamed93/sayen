<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Notification;

class NotificationController extends Controller
{
    
    public function notificationSeen(Request $request, $notification_id)
    {
        if($request->ajax())
        {
            $notification = Notification::find($notification_id);
            if($notification)
            {
                $notification->seen = '1';
                $notification->save();
                return response()->json(['message' => trans('admin.success_save')], 200);    
            }
        }
    }

}
