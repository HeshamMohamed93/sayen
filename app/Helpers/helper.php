<?php

    use LaravelFCM\Message\OptionsBuilder;
    use LaravelFCM\Message\PayloadDataBuilder;
    use LaravelFCM\Message\PayloadNotificationBuilder;
    use App\Notification;
    use App\User;
    use App\Team;
    use App\Order;
    use App\EmergencyOrder;

    // === Upload image to  folder === 
    function uploadImage($image, $dir)
    {
        $extension = $image->getClientOriginalExtension();
        $imageRename = mt_rand(100000000, 999999999).'.'.$extension;
        $img = Image::make($image)->resize(null, 700, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        })->save('public/uploads/'.$dir.'/'.$imageRename);

        return $imageRename;
    }
    // === End function ===

    // === Dekete image from folder ===
    function deleteImage($image, $dir)
    {
        $path = 'public/uploads/'.$dir.'/'.$image;
        File::delete($path);
    }
    // === End function ===
    
    // === Validate phone number to ensure valid in KSA formate ===
    function PhoneFormateForDB($phone=null,$country_code=null) // this for insert into DB or Check in DB
    { 
        $phone = arTOen($phone);
        $country_code = arTOen($country_code);

        $is_valid_phone = preg_match('/^(009665|00966|966|9665|\+966|\+9665|05|5)(5|0|3|6|4|9|1|8|7)([0-9]{7,10})$/', $phone);
        
        if ($is_valid_phone && ($country_code == '966' || $country_code == '00966' || $country_code == '+966')) 
        {
          return '966'.preg_replace('/^(00966|009660|966|\+966|0){1,6}/', '', $phone);
        }
        else
        {
          return false;
        }
    }
    // === End function ===

    // === From AR num to EN num ===
    function arTOen($string) 
    {
        return strtr($string, array('۰'=>'0', '۱'=>'1', '۲'=>'2', '۳'=>'3', '۴'=>'4', '۵'=>'5', '۶'=>'6', '۷'=>'7', '۸'=>'8', '۹'=>'9', '٠'=>'0', '١'=>'1', '٢'=>'2', '٣'=>'3', '٤'=>'4', '٥'=>'5', '٦'=>'6', '٧'=>'7', '٨'=>'8', '٩'=>'9'));
    }
    // === End function ===

    //=== Send Notification Function ===
    function sendNotification($notification_data, $player_id, $notification_id = null)
    {
        
        $optionBuilder = new OptionsBuilder();
        $optionBuilder->setTimeToLive(60*20);

        $notificationBuilder = new PayloadNotificationBuilder(trans('api.app_name')); 
        $notificationBuilder->setBody($notification_data['message'])
                            ->setIcon(asset('public/img/'. $notification_data['image']))
                            ->setSound('default');
                            
        $dataBuilder = new PayloadDataBuilder();
        
        if(isset($notification_data['order_id']))
        {
            $dataBuilder->addData(['order_id' => $notification_data['order_id']]);
        }
        else 
        {
            $dataBuilder->addData(['data' => $notification_data['data'], 'notification_id' => $notification_id]);
        }
        
        $option = $optionBuilder->build();
        $notification = $notificationBuilder->build();
        $data = $dataBuilder->build();
        $token = $player_id;
        if($token == null)
        {
            return false;
        }
        $downstreamResponse = FCM::sendTo($token, $option, $notification, $data);
        
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
        return $downstreamResponse;
    }
    //=== End Function ===

    // === Create notification ===
    function createNotification($notification_data)
    {
        $create_notification = Notification::create($notification_data);

        if($create_notification)
        {
            sendNotification($notification_data, getPlayerID($create_notification->user_id, $create_notification->user_type), $create_notification->id);
        }
    }
    // === End function ===

    // === Get user player id ===
    function getPlayerID($user_id, $user_type)
    {
        
        if($user_type == 1) //=== user
        {
            return User::where('id', $user_id)->pluck('player_id')[0];
        }
        else if($user_type == 2) //=== team
        {
            return Team::where('id', $user_id)->pluck('player_id')[0];
        }
    }
    function checkSeenOrder(){
        $seen = Order::where('seen',0)->count();
        if($seen > 0){
            return 1;
        }else{
            return 0;
        }
    }
    function checkSeenEmergencyOrder(){
        $seen = EmergencyOrder::where('seen',0)->count();
        if($seen > 0){
            return true;
        }else{
            return false;
        }
    }
    // === End function ===

?>