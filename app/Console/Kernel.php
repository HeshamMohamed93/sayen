<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Order;
use App\User;
use Carbon\Carbon;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            $now = Carbon::now();
            $today_date = Carbon::today()->toDateString();
        
            $today_orders = Order::where([['status', '5'], ['visit_date', 'like', '%'.$today_date.'%'], ['alert_before_visit', '0']])->get();

            foreach($today_orders as $order)
            {
                $visit_time = Carbon::parse($order->visit_date);
                $reminder_hours = $visit_time->diff($now)->format('%H');
                
                if($reminder_hours <= 1)
                {
                    $order->alert_before_visit = '1';
                    $order->save();
                    $user = User::find($order->user_id);
                    $notification_data['image'] = 'default_service.png';    
                    $notification_data['order_id'] = $order->id;
                    $notification_data['user_id'] = $order->user_id;
                    $notification_data['user_type'] = '1';   
                    $notification_data['message'] = trans('notification.user_order_reminder_notification',[],$user->device_lang);   

                    createNotification($notification_data);
                }
            }
        })->everyFiveMinutes();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
