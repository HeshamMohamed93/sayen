<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Admin;
use App\OrderInvoice;
use Auth;
use DB;
use App\Order;
use App\Setting;
use App\Notification;

class HomeController extends Controller
{
    public function index()
    {
        $users = DB::table('users')->where('deleted_at','=',NULL)->get()->count();
        $services = DB::table('services')->where('deleted_at','=',NULL)->get()->count();
        $total_profit = OrderInvoice::whereHas('order', function($q){
                            $q->where('status', '3');
                        })->where('team_receive_money', '1')->sum('final_price');
        $total_orders = DB::table('orders')->count();
        $total_pay_cache = DB::table('orders')->where('pay_method', '1')->count();
        $total_pay_online = DB::table('pay_online_transactions')->count();
        $orders = DB::table('orders')->select(DB::raw('count(*) as total, date(visit_date) as visit_date'))->groupBy(DB::raw('date(visit_date)'))->where('deleted_at',null)->get();
        $emergency_orders = DB::table('emergency_orders')->select(DB::raw('count(*) as total, date(created_at) as visit_date'))->groupBy(DB::raw('date(created_at)'))->where('deleted_at',null)->get();
        
        return view('admin.home.home', compact('emergency_orders','users', 'services', 'total_profit', 'total_orders', 'total_pay_cache', 'total_pay_online', 'orders'));
    }
    public function sendTestSms(){
        $curl = curl_init();
        $app_id = "7nfgGwaqDEuzlKV9pmN6wVw6x0MWpKkTmOnhnZaF";
        $app_sec = "jU8or1HwwUnL8ZLLaE8pcAxXRWZYPEKKCrU8q8TsDmU7RJ9SnKy0YpMnixo2cEXgaRyUEQdXvAZUVnlMeSMJIQqD6SHH4L9KIdC9";
        $app_hash  = base64_encode("$app_id:$app_sec");
        $messages = [];
        $messages["messages"] = [];
        $messages["messages"][0]["text"] = "test message";
        $messages["messages"][0]["numbers"][] = "966537711282";
        $messages["messages"][0]["sender"] = "SAYEN.APP";

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api-sms.4jawaly.com/api/v1/account/area/sms/send',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>json_encode($messages),
            CURLOPT_HTTPHEADER => array(
                'Accept: application/json',
                'Content-Type: application/json',
                'Authorization: Basic '.$app_hash
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        dd(json_decode($response));
    }
    public function notificationReload(){

        $order_notification_link_type = '';
        $admin = new \App\Http\Middleware\Admin;
        $ordr_notification_permission = $admin->permissionForSelectedModule(Auth::user()->id, 'orders');
        $notifications =  DB::table('notifications')->where([['user_type', '3'], ['seen', '0']])->orderBy('id', 'DESC')->get();

        if($ordr_notification_permission->can_show == 1 && $ordr_notification_permission->can_edit == 1)
        {
            $order_notification_link_type = 'orders.show';
        }
        else if($ordr_notification_permission->can_show == 1 && $ordr_notification_permission->can_edit == 0)
        {
            $order_notification_link_type = 'orders.show';
        }
        else if($ordr_notification_permission->can_show == 0 && $ordr_notification_permission->can_edit == 1)
        {
            $order_notification_link_type = 'orders.edit';
        }
        else
        {
            $notifications = [];
        }


        //$notifications =  DB::table('notifications')->where([['user_type', '3'], ['seen', '0']])->orderBy('id', 'DESC')->get();
        return response()->json(view('admin.notificationReload',compact('notifications','order_notification_link_type'))->render());
    }
    public function notificationReadAll(){
        Notification::where('seen','0')->where('user_type','3')->update(['seen'=>'1']);
    }
    public function showInvoice($id){
        $order = Order::find($id);
        if($order){
            $setting = Setting::select('value_added')->first();
            $totalBefore =  (($order->hand_work + array_sum($order->orderInvoice->teamAddedPrice())) - $order->orderInvoice->coupon_discount );
            $tax = ($totalBefore *  $setting->value_added ) / 100;
            $totalAfter = $totalBefore + $tax;
            return view('admin.showInvoice',compact('order','setting','totalAfter','totalBefore','tax'));
        }else{
            return redirect('admin-panel');
        }
    }

}
