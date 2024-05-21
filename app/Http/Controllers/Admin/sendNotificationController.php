<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use Carbon\Carbon;
use App\User;
use DB;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;

class sendNotificationController extends Controller
{
    // === Send notifications form ===
    public function index(Request $request)
    {
        if(Auth::user()->permissions)
        {
            if(Auth::user()->permissions->can_create)
            {
                $users = User::where('active',1)->orderBy('name')->get();
                $page_title = trans('admin.send_notifications');
                $method = 'get';
                $submit_action = url('send-notification.sendNotification');

                return view('admin.send_notification.form', compact('page_title', 'method', 'submit_action', 'users'));
            }
            else
            {
                return redirect()->route('home'); 
            }
        }
        else
        {
            return redirect()->route('home'); 
        }      
    }
    // === End function ===
    // === send notifications  ===
    public function sendNotification(Request $request)
    {
        $optionBuilder = new OptionsBuilder();
        $optionBuilder->setTimeToLive(60*20);

        $notificationBuilder = new PayloadNotificationBuilder(trans('api.app_name')); 
        $notificationBuilder->setBody($request->text)
                            ->setSound('default');
                            
        $dataBuilder = new PayloadDataBuilder();
        
       
        $dataBuilder->addData(['type' =>'public']);
        
        $option = $optionBuilder->build();
        $notification = $notificationBuilder->build();
        $data = $dataBuilder->build();
        if(isset($request->users)){
            $tokens = User::where('player_id','!=','')->whereIN('id',$request->users)->pluck('player_id')->toArray();
        }elseif(isset($request->selectAll)){
            $tokens = User::where('player_id','!=','')->pluck('player_id')->toArray();
        }else{
            return redirect()->back();
        }
        if(count($tokens) == 0){
            session()->flash('notification_message_error',trans('admin.notification_message_error'));
            return redirect()->back();
        }
        $downstreamResponse = FCM::sendTo($tokens, $option, $notification, $data);
        $downstreamResponse->numberSuccess();
        $downstreamResponse->numberFailure();
        $downstreamResponse->numberModification();
        
        // return Array - you must remove all this tokens in your database
        $downstreamResponse->tokensToDelete();
        
        // return Array (key : oldToken, value : new token - you must change the token in your database)
        $downstreamResponse->tokensToModify();
        
        // return Array - you should try to resend the message to the tokens in the array
        $downstreamResponse->tokensToRetry();
        
        // return Array (key:token, value:error) - in production you should remove from your database the tokens
        $downstreamResponse->tokensWithError();
        session()->flash('notification_message',trans('admin.notification_message'));
        return back();
    }
    // === End function ===
}
