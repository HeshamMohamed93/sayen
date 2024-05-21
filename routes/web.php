<?php

Route::get('/', 'Frontend\HomeController@index')->name('index');
Route::get('delete_account_request','Frontend\HomeController@delete_account_request');
Route::post('delete_account_request','Frontend\HomeController@post_delete_account_request');

Route::post('send-message', 'Frontend\HomeController@sendMessage')->name('send-message');

// === Privacy Policy ===
Route::get('/privacy_policy', 'Frontend\HomeController@privacyPolicy');

Route::get('pay', 'TestPayment@payForm');

// === Auth route ===
Route::group(['prefix' => 'admin-panel'], function () {
    Auth::routes(['register' => false]);
});

// === Admin panel routes ===
Route::group(['prefix' => 'admin-panel', 'middleware' => ['auth'], 'namespace' => 'Admin'], function () {

    //return url('https://sayen.co/');
    Route::group(['middleware' => 'admin'], function() {
        
        // === Home ===
        Route::get('/home', 'HomeController@index')->name('home');

        Route::get('/sendTestSms', 'HomeController@sendTestSms')->name('sendTestSms');

        // === Admin ===
        Route::resource('admins', 'AdminController');

        // === Service ===
        Route::resource('services', 'ServiceController');

        // === Emergency Orders ===
        Route::resource('emergency-services', 'EmergencyServiceController');

        // === Offers ===
        Route::resource('offers', 'OfferController');

        // === Teams ===
        Route::resource('teams', 'TeamController');

        // === Orders ===
        Route::resource('orders', 'OrderController');
        Route::get('edit-order-up/{type}/{id}', 'OrderController@editOrderUp')->name('edit-order-up');

        // === Emergency Orders ===
        Route::resource('emergency-orders', 'EmergencyOrderController');

        // === Warranty Orders ===
        Route::resource('warranty-orders', 'WarrantyOrderController');
         
        // === Users ===
        Route::resource('users', 'UserController');

        // === logs ===
        Route::resource('logs', 'LogController');

        // === Coupons ===
        Route::resource('coupons', 'CouponController');

        // === Common questions ===
        Route::resource('common-questions', 'CommonQuestionController');

        // === Static pages ===
        Route::resource('static-pages', 'StaticPageController');

        // === profile ===
        Route::get('profile', 'AdminController@profile')->name('profile');

        // === Report problem ===
        Route::resource('report-problems', 'ReportProblemController');

        // === Contact us ===
        Route::resource('contact-us', 'ContactUsController');

        // === Settings ===
        Route::resource('settings', 'SettingController');

        // === Features ===
        Route::resource('features', 'FeatureController');

        // === Buildings ===
        Route::resource('buildings', 'BuildingController');

        // === Buildings ===
        Route::resource('units', 'UnitController');

        // === Report ===
        Route::resource('sales-report', 'SalesReportController');
        Route::get('export-sales-report', 'SalesReportController@exportReport')->name('export-sales-report');
        Route::resource('bankup-report', 'BankUpReportController');
        Route::get('export-bankup-report', 'BankUpReportController@exportReport')->name('export-bankup-report');
        Route::resource('maintenance-report', 'MaintenanceReportController');
        Route::get('export-maintenance-report', 'MaintenanceReportController@exportReport')->name('export-maintenance-report');
        Route::get('edit-maintenance-report/{type}/{id}', 'MaintenanceReportController@editReport')->name('edit-maintenance-report');
        Route::resource('team-report', 'TeamReportController');
        Route::get('export-team-report', 'TeamReportController@exportReport')->name('export-team-report');
        // send Notification
        Route::resource('send-notification','sendNotificationController');
        Route::get('sendNotification','sendNotificationController@sendNotification')->name('sendNotification');
        Route::resource('order-up', 'OrderUpController');
    });
    Route::get('/notification-reload', 'HomeController@notificationReload')->name('notification-reload');
    Route::post('/notification-readAll', 'HomeController@notificationReadAll')->name('notification-readAll');
    Route::get('/show-invoice/{id}', 'HomeController@showInvoice')->name('show-invoice');
    Route::get('user-change-status/{id}/{status}', 'UserController@changeStatus')->name('user-change-status');
    Route::get('team-change-status/{id}/{status}', 'TeamController@changeStatus')->name('team-change-status');
    Route::get('coupon-change-status/{id}/{status}', 'CouponController@changeStatus')->name('coupon-change-status');
    Route::get('service-teams', 'OrderController@serviceTeams')->name('service-teams');
    Route::put('/update-profile', 'AdminController@updateProfile')->name('update-profile');
    Route::get('/send-invoice/{order_id}', 'OrderController@sendInvoice')->name('send-invoice');
    Route::post('/cancelOrder/{order_id}/{problem}', 'OrderController@cancelOrder')->name('cancelOrder');
    Route::get('/refund/{order_id}', 'OrderController@refund')->name('refund');
    Route::get('/notification-seen/{notification_id}', 'NotificationController@notificationSeen')->name('notification-seen');
    Route::post('services-stoping', 'ServiceController@serviceStop');
    Route::post('print-maintenanance','AdminController@printMaintenanance');
    Route::post('print-onepage-maintenanance','AdminController@printOnePageMaintenanance');
    Route::post('save-edit-maintenanance-report','AdminController@saveEditMaintenananceReport');
    Route::post('save-edit-order-up','OrderController@saveEditOrderUp');
    Route::post('print-order-up','OrderController@printOrderUp');
    Route::post('print-onepage-order','OrderController@printOnePageOrder');
    Route::get('/logout', 'AdminController@logout')->name('logout');
    Route::post('changeService', 'AdminController@changeService');
    Route::post('offerSendNotification', 'AdminController@offerSendNotification');
    Route::post('remove-test-orders', 'OrderController@removeTestOrders');
    Route::delete('delete-one-image/{id}', 'AdminController@deleteOneImage');
});
