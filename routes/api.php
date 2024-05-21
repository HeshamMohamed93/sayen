<?php


Route::group(['namespace' => 'Api\V1'], function(){

    // === User routes ===
    Route::group(['prefix' => 'v1/user'], function() {
       
        // === Auth routes ===
        Route::post('login', 'AuthController@userLogin');
        Route::post('register', 'AuthController@userRegister');
        Route::put('update-profile', 'AuthController@userUpdateProfile');
        Route::get('buildings', 'AuthController@buildings');
        // === End auth routes ===

        // === Order routes ===
        Route::resource('order', 'User\OrderController')->except(['edit', 'destroy', 'update']);        
        Route::post('cancel-order', 'User\OrderController@cancelOrder');        
        Route::post('rate-order', 'User\OrderController@rateOrder');        
        Route::post('validate-coupon', 'User\OrderController@validateCoupon');        
        Route::post('accept-added-price', 'User\OrderController@acceptAddedPrice');   
        Route::get('success-pay', 'User\OrderController@successPay');
        Route::get('redirect-pay', 'User\OrderController@redirectAfterPay')->name('redirect-pay');
        // === End order routes ===

        // === Contact us ===
        Route::post('send-message', 'User\ContactUsController@sendMessage');
        // === End order routes ===
    });
    // === End user routes ===

    // === Team routes ===
    Route::group(['prefix' => 'v1/team'], function() {
        
        // === Auth routes ===
        Route::post('login', 'AuthController@teamLogin');
        Route::put('update-profile', 'AuthController@teamUpdateProfile');
        Route::put('update-location', 'AuthController@updateLocation');
        // === End auth routes ===
        
        // === Order routes ===
        Route::resource('order', 'Team\OrderController')->except(['edit', 'destroy', 'update']);
        Route::put('go-work', 'Team\OrderController@goWork');
        Route::put('start-work', 'Team\OrderController@startWork');
        Route::put('end-work', 'Team\OrderController@endWork');
        Route::put('finish-work', 'Team\OrderController@finishWork');
        // === End order routes ===

        // === Invoice routes ===
        Route::put('add-price', 'Team\InvoiceController@addPricing');
        Route::get('invoices', 'Team\InvoiceController@index');
        // === End order routes ===

        // === Report problem routes===
        Route::get('report-problem-types', 'Team\ProblemController@problemTypes');
        Route::post('report-problem', 'Team\ProblemController@reportProblem');
        // === End report problem routes ===

    });
    // === End team routes ===

    // === Common routes ===
    Route::post('v1/verify-code', 'AuthController@verifyCode');
    Route::post('v1/forget-password', 'AuthController@forgetPassword');
    Route::put('v1/change-password', 'AuthController@changePassword');
    Route::post('v1/upload-profile-image', 'AuthController@updateProfilePicture');
    Route::post('v1/resend-code', 'AuthController@resendCode');
    Route::post('v1/change-phone', 'AuthController@changePhone');
    Route::get('v1/profile', 'AuthController@profile');
    Route::post('v1/logout', 'AuthController@logout');
    Route::put('v1/set-player-id', 'AuthController@createPlayerID');
    Route::get('v1/notifications', 'NotificationController@getNotifications');
    Route::put('v1/notification-seen', 'NotificationController@setNotificationToSeen');  
    Route::get('v1/services', 'CommonServiceController@getServices');      
    Route::get('v1/common-questions', 'CommonServiceController@getCommonQuestions');
    Route::get('v1/static-page/{id}', 'CommonServiceController@getStaticPage');
    Route::get('v1/check-status', 'CommonServiceController@checkStatus');
    // === End common routes ===
    

});


Route::group(['namespace' => 'Api\V2'], function(){

    Route::group(['prefix' => 'v2/user'], function() {
       
        // === Auth routes ===
        Route::post('login', 'AuthController@userLogin');
        Route::post('register', 'AuthController@userRegister');
        Route::put('update-profile', 'AuthController@userUpdateProfile');
        Route::get('buildings', 'AuthController@buildings');
        Route::post('createOTP', 'AuthController@createOTPUser');
        // === End auth routes ===
    
        // === Order routes ===
        Route::resource('order', 'User\OrderController')->except(['edit', 'destroy', 'update']);        
        Route::post('cancel-order', 'User\OrderController@cancelOrder'); 
        Route::post('checkTimeDate', 'User\OrderController@checkTimeDate'); 
        Route::post('checkDate', 'User\OrderController@checkDate'); 
        Route::post('rate-order', 'User\OrderController@rateOrder');        
        Route::post('validate-coupon', 'User\OrderController@validateCoupon');        
        Route::post('accept-added-price', 'User\OrderController@acceptAddedPrice');   
        Route::get('success-pay', 'User\OrderController@successPay');
        Route::get('redirect-pay', 'User\OrderController@redirectAfterPay')->name('redirect-pay');
        Route::get('pay-form/{order_id}/{device_type}', 'User\OrderController@payForm')->name('pay-form');
        // === End order routes ===
    
        // === Emergency Order routes ===
        Route::resource('emergency-order', 'User\EmergencyOrderController')->except(['edit', 'destroy', 'update']);
        // === End Emergency order routes ===

        // === warranty Order routes ===
        Route::resource('warranty-order', 'User\WarrantyOrderController')->except(['edit', 'destroy', 'update']);
        // === End warranty order routes ===

        // === Contact us ===
        Route::post('send-message', 'User\ContactUsController@sendMessage');
        // === End order routes ===
    });
    // === End user routes ===
    
    // === Team routes ===
    Route::group(['prefix' => 'v2/team'], function() {
        
        // === Auth routes ===
        Route::post('login', 'AuthController@teamLogin');
        Route::put('update-profile', 'AuthController@teamUpdateProfile');
        Route::put('update-location', 'AuthController@updateLocation');
        // === End auth routes ===
        
        // === Order routes ===
        Route::resource('order', 'Team\OrderController')->except(['edit', 'destroy', 'update']);
        Route::put('go-work', 'Team\OrderController@goWork');
        Route::put('start-work', 'Team\OrderController@startWork');
        Route::put('end-work', 'Team\OrderController@endWork');
        Route::post('finish-work', 'Team\OrderController@finishWork');
        Route::post('add-service', 'Team\OrderController@addService');
        Route::post('add-material', 'Team\OrderController@addMaterial');
        // === End order routes ===

        // === ُmergency Order routes ===
        Route::get('emergency-order', 'Team\OrderController@emergencyOrder');
        Route::get('emergency-order/{id}', 'Team\OrderController@showEmergencyOrder');
        Route::put('emergency-order/go-work', 'Team\EmergencyOrderController@goWork');
        Route::put('emergency-order/start-work', 'Team\EmergencyOrderController@startWork');
        Route::put('emergency-order/end-work', 'Team\EmergencyOrderController@endWork');
        Route::post('emergency-order/finish-work', 'Team\EmergencyOrderController@finishWork');
        Route::post('emergency-order/add-service', 'Team\EmergencyOrderController@addService');
        Route::post('emergency-order/add-material', 'Team\EmergencyOrderController@addMaterial');
        // === End emergency order routes ===

        // === Invoice routes ===
        Route::put('add-price', 'Team\InvoiceController@addPricing');
        Route::get('invoices', 'Team\InvoiceController@index');
        // === End order routes ===
    
        // === Report problem routes===
        Route::get('report-problem-types', 'Team\ProblemController@problemTypes');
        Route::post('report-problem', 'Team\ProblemController@reportProblem');
        // === End report problem routes ===
        
    });
    // === End team routes ===
    Route::get('v2/makePdf/{id}','CommonServiceController@makePdf');
    // === Common routes ===
    Route::post('v2/verify-code', 'AuthController@verifyCode');
    Route::post('v2/forget-password', 'AuthController@forgetPassword');
    Route::put('v2/change-password', 'AuthController@changePassword');
    Route::post('v2/upload-profile-image', 'AuthController@updateProfilePicture');
    Route::post('v2/resend-code', 'AuthController@resendCode');
    Route::post('v2/change-phone', 'AuthController@changePhone');
    Route::get('v2/profile', 'AuthController@profile');
    Route::post('v2/logout', 'AuthController@logout');
    Route::put('v2/set-player-id', 'AuthController@createPlayerID');
    Route::get('v2/notifications', 'NotificationController@getNotifications');
    Route::put('v2/notification-seen', 'NotificationController@setNotificationToSeen');  
    Route::get('v2/services', 'CommonServiceController@getServices');  
    Route::post('v2/subServices', 'CommonServiceController@getSubServices');      
    Route::get('v2/common-questions', 'CommonServiceController@getCommonQuestions');
    Route::get('v2/static-page/{id}', 'CommonServiceController@getStaticPage');
    Route::get('v2/check-status', 'CommonServiceController@checkStatus');
    Route::get('v2/offers', 'CommonServiceController@getOffers');  
    Route::get('v2/show-invoice/{id}','CommonServiceController@showInvoice');
    Route::post('v2/send-sms','CommonServiceController@testsendSms');
    // === End common routes ===
});
    

