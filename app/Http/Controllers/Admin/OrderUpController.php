<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Routing\Route;
use App\Http\Requests\Admin\OrderRequest;
use Auth;
use App\Team;
use App\Service;
use App\Order;
use DB;
use Carbon\Carbon;
use App\User;
use App\PayOnlineTransaction;
use App\Refund;
use DateTime;
use App\OrderInvoice;
use App\TeamService;
use App\ReportProblem;
use App\AdminService;
use App\OrderUp;

class OrderUpController extends Controller
{
    private $model;
    private $view_folder;
    private $submit_action;
    private $page_title;
    private $upload_folder;
    private $redirect_url;
    private $success_save;
    private $success_delete;
    private $prefix;

    function __construct(Request $request, Route $route, order $order)
    {
        $this->prefix = 'order-up.';
        //$this->model = $order;
        $this->upload_folder = 'orders';
        $this->view_folder = 'admin.orders.';
        $this->icon = 'fa fa-user-secret';

        $this->success_save = trans('admin.success_save');
        $this->redirect_url = route($this->prefix.'index');

        $current_method = $route->getActionMethod();

        if($current_method == 'index')
        {
            $this->page_title = trans('admin.order_up');
        }
    }

    // === Get all orders ===
    public function index(Request $request)
    {
        if(Auth::user()->permissions)
        {
            if(Auth::user()->permissions->can_create || Auth::user()->permissions->can_edit
                || Auth::user()->permissions->can_show || Auth::user()->permissions->can_delete)
            {
                $orders = OrderUp::get();
                $page_title = $this->page_title;
                return view($this->view_folder.'orderUp', compact('orders','page_title'));
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

    // === Create new service page ===
    public function create()
    {
        if(Auth::user()->permissions)
        {
            if(Auth::user()->permissions->can_create)
            {
                $page_title = $this->page_title;
                $method = 'post';
                $submit_action = $this->submit_action; 
                $services = Service::where('parent_id',0)->where('active',1)->pluck('name','id')->toArray();
                $users = User::select('name','id','phone')->get();
                $images = DB::table('images_maintenanance_report')->get();
                $teams = Team::get();
                return view($this->view_folder.'addOrderUp', compact('page_title', 'method', 'submit_action','services','users','images','teams'));
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


    // === Edit existing service ===
    public function edit($id)
    {
        if(Auth::user()->permissions)
        {
            if(Auth::user()->permissions->can_edit)
            {
                $services = Service::where('parent_id',0)->where('active',1)->pluck('name','id')->toArray();
                $users = User::select('name','id','phone')->get();
                $images = DB::table('images_maintenanance_report')->get();
                $teams = Team::get();
                $order = OrderUp::find($id);
                //$order['visit_date'] = Carbon::createFromFormat('Y-m-d H:i:s', $order['visit_date']);
                $method = 'put';
                $submit_action = route($this->prefix.'update', $id);
                $page_title = $this->page_title;

                $start_time = Carbon::parse($order->team_start_at);
                $finish_time = Carbon::parse($order->team_end_at);
                $servicesArray = DB::table('order_up_services')->where('order_id',$id)->pluck('service_id')->toArray();
                return view($this->view_folder.'editOrderUp', compact('services','servicesArray' ,'order', 'page_title', 'submit_action', 'method', 'teams','users','images'));
            }
        }
    }
    // === End function ===

    // === Confirm edit service ===
    public function update(Request $request, $id)
    {
        if(Auth::user()->permissions)
        {
            if(Auth::user()->permissions->can_edit)
            {
                
                $order = OrderUp::where('id', $id)->first();
                $request['visit_date'] = Carbon::parse(date_format(date_create($request->visit_date),'Y-m-d H:i:s'));
                $order->team_id = $request->team_id;
                $order->user_id = $request->user_id;
                $order->visit_date = $request->visit_date;
                $order->work_details = $request->work_details;
                $order->hand_work = $request->hand_work;
                $order->hand_work_price = $request->hand_work_price;
                $order->materials_used = $request->materials_used;
                $order->materials_used_price = $request->materials_used_price;
                $order->save();
                DB::table('order_up_services')->where('order_id',$order->id)->delete();
                $services = $request->service_id;
                foreach($services as $service){
                    DB::table('order_up_services')->insert(['order_id' => $order->id,'service_id' => $service]);
                }
                $url = url("admin-panel/order-up/$order->id/edit");
                return response()->json(['redirect' => $url, 'message' => $this->success_save], 200);
            }
        }
    }
    // === End function ===

    // === Filter teams for selected service ===
    public function serviceTeams(Request $request)
    {
        if($request->ajax())
        {
            $teamIDs = TeamService::where('service_id',$request->service_id)->pluck('team_id')->toArray();
            $teams = Team::whereIN('id', $teamIDs)->where('active', '1')->get();
            return response()->json(['teams' => $teams], 200);
        }
    }
    // === End function ===

    // === Create notification ===
    private function createOrderNotification($order, $team_notification = [], $user_notification_msg = null)
    {
        $notification_data['order_id'] = $order['id'];
        $notification_data['image'] = 'default_service.png';
        $user = User::find($order['user_id']);
        $team = Team::find($order['team_id']);

        switch ($order->status)
        {
            case 5: //=== assigned to team
                $notification_data['user_id'] = $order['user_id'];
                $notification_data['user_type'] = '1';
                $notification_data['message'] = $user_notification_msg;
                
                createNotification($notification_data);
                $notification_data['user_id'] = $order['team_id'];
                $notification_data['user_type'] = '2';
                $notification_data['message'] =  $team_notification['msg'];
                $notification_data['image'] =  $team_notification['image'];
                createNotification($notification_data);
            break;

            case 3: //=== done
                $notification_data['user_id'] = $order['user_id'];
                $notification_data['user_type'] = '1';
                $notification_data['message'] = trans('notification.team_close_order', ['value' => $order['order_number']],$user->device_lang);
                createNotification($notification_data);
            break;

            case 4: //=== cancel
                $notification_data['user_id'] = $order['user_id'];
                $notification_data['user_type'] = '1';
                $notification_data['message'] = trans('notification.team_cancel_service_notification',[],$user->device_lang);
                createNotification($notification_data);

                if($order['team_id'] != null)
                {
                    $notification_data['user_id'] = $order['team_id'];
                    $notification_data['user_type'] = '2';
                    $notification_data['message'] = trans('notification.team_cancel_service_notification',[],$team->device_lang);
                    createNotification($notification_data);
                }
            break;
        }
    }
    // === End function ===

    // === Send invoice to user ===
    public function sendInvoice(Request $request, $order_id)
    {
        if($request->ajax())
        {
            $order = $this->model::where([['id', $order_id], ['status', '3']])->first();
            $user = User::find($order->user_id);
            if($order)
            {
                $notification['order_id'] = $order->id;
                $notification['user_id'] = $order->user_id;
                $notification['user_type'] = '1';
                $notification['message'] = trans('notification.admin_send_invoice', ['value' => $order->order_number],$user->device_lang);
                $notification['image'] = 'invoice.png';

                createNotification($notification);

                return response()->json(['redirect' => $this->redirect_url, 'message' => $this->success_save], 200);
            }
        }
    }
    // === End function ===

    // === Refund ===
    public function refund(Request $request, $order_id)
    {
        if($request->ajax())
        {
            $order = $this->model::where([['id', $order_id], ['status', '4']])->first();

            if($order)
            {

                $earlier = new DateTime($order->visit_date);
                $later = new DateTime($order->cancelled_at);
                $diff = $later->diff($earlier)->format("%a")*24;

                if($diff < 24)
                {
                    return response()->json(['errors' => 'لا يستحق استرجاع المبلغ باقي من الوقت اقل من 24 ساعة'], 400);
                }
                else
                {
                    $already_refunded = Refund::where('order_id', $order_id)->first();
                    if($already_refunded)
                    {
                        return response()->json(['errors' => 'تم استرجاع المبلغ من قبل'], 400);
                    }

                    $transaction = PayOnlineTransaction::where('order_id', $order_id)->first();
                    $paymentService = new \Moyasar\Providers\PaymentService();
                    $payment = $paymentService->fetch($transaction->reference_id);
                    $refund = $payment->refund($transaction->pay_amount*100);

                    Refund::create([
                        'order_id' => $order_id,
                        'refund_amount' => $transaction->pay_amount,
                    ]);

                    return response()->json(['redirect' => $this->redirect_url, 'message' => $this->success_save], 200);

                }

            }
        }
    }
    // === End function ===
    public function removeTestOrders(){
        Order::where('user_id',2164)->delete();
        return response()->json(['redirect' => $this->redirect_url, 'message' => trans('admin.success_delete')], 200);
    }
    public function editOrderUp($type,$id){
        
        if($type == 'order_normal'){
            $order = Order::find($id);
            $type = 'order';
        }else{
            $order = EmergencyOrder::find($id);
            $type = 'emergencyorder';
        }
        //$manitenanceReport = DB::table('maintenanance_report')->where('type',$type)->where('order_id',$order->id)->first();
        $services = Service::pluck('name','id')->toArray();
        $users = User::select('name','id','phone')->get();
        $images = DB::table('images_maintenanance_report')->get();
        return view('admin.orders.orderUp',compact('order','type','images','services','users'));
    }
    public function saveEditOrderUp(Request $request){
        
        $check = DB::table('order_up')->where('type',$request->type)->where('order_id',$request->id)->first();
        if($check){
            DB::table('order_up')->where('id',$check->id)->update(['work_details'=>$request->work_details,'hand_work'=>$request->hand_work,'hand_work_price'=>$request->hand_work_price,'materials_used'=>$request->materials_used,'materials_used_price'=>$request->materials_used_price,'type'=>$request->type,'image'=> $request->image_id,'user_id' =>$request->user_id,'service_id'=>$request->service_id]);
        }else{
            DB::table('order_up')->insert(['order_id'=>$request->id,'work_details'=>$request->work_details,'hand_work'=>$request->hand_work,'hand_work_price'=>$request->hand_work_price,'materials_used'=>$request->materials_used,'materials_used_price'=>$request->materials_used_price,'type'=>$request->type,'image'=>$request->image_id,'user_id' =>$request->user_id,'service_id'=>$request->service_id]);
        }
        if($request->type == 'order'){
            $url = $request->id;
        }else{
            $url = $request->id;
        }
        return response()->json(['redirect' => $url, 'message' => trans('admin.success_add')], 200);
    }
    public function printOrderUp(Request $request){
        // Fetch all customers from database
        $path = base_path().'/public/uploads/';
        if($request->type == 'order'){
            $data = Order::where('id', $request->id)->first();
        }else{
            $data = EmergencyOrder::where('id', $request->id)->first();
        }
        $workDetails = $request->workDetails;
        $handWork = $request->handWork;
        $materialsUsed = $request->materialsUsed;
        $handWorkPrice = $request->handWorkPrice;
        $materialsUsedPrice = $request->materialsUsedPrice;
        $client = User::find($request->user_id);
        $service = Service::find($request->service_id);

        $report = DB::table('order_up')->where('type',$request->type)->where('order_id',$request->id)->first();
        if($report){
            $check = DB::table('images_maintenanance_report')->where('id',$report->image)->first();
            if($check){
                $image = $check->image;
            }else{
                $image = null;
            }
        }else{
            $image = null;
        }

        set_time_limit(300);
        ini_set('max_execution_time', '300');
        ini_set("pcre.backtrack_limit", "5000000");
        $html = view('admin.print_order_pdf',compact('data','workDetails','handWork','materialsUsed','image','handWorkPrice','materialsUsedPrice','client','service'))->render(); // file render
        $mpdf = new \Mpdf\Mpdf(['tempDir' => storage_path('/tmp')]);
        $mpdf->WriteHTML($html);
        $mpdf->Output($path.'print_order_'.$data->order_number.'.pdf','F');
        $file_path = 'uploads/print_order_'.$data->order_number.'.pdf';
        return response()->json(['path'=>'https://sayen.co/public/uploads/print_order_'.$data->order_number.'.pdf']);
    }
    public function printOnePageOrder(Request $request){
        if($request->type == 'order'){
            $data = Order::where('id', $request->id)->first();
        }else{
            $data = EmergencyOrder::where('id', $request->id)->first();
        }
        $workDetails = $request->workDetails;
        $handWork = $request->handWork;
        $materialsUsed = $request->materialsUsed;
        $handWorkPrice = $request->handWorkPrice;
        $materialsUsedPrice = $request->materialsUsedPrice;
        $client = User::find($request->user_id);
        $service = Service::find($request->service_id);

        $report = DB::table('maintenanance_report')->where('type',$request->type)->where('order_id',$request->id)->first();
        if($report){
            $check = DB::table('images_maintenanance_report')->where('id',$report->image)->first();
            if($check){
                $image = $check->image;
            }else{
                $image = null;
            }
        }else{
            $image = null;
        }
        set_time_limit(300);
        ini_set('max_execution_time', '300');
        ini_set("pcre.backtrack_limit", "5000000");
        $html = view('admin.print_order_pdf',compact('data','workDetails','handWork','materialsUsed','image','handWorkPrice','materialsUsedPrice','client','service'))->render(); // file render
        return response()->json(['data' => $html, 'message' => trans('admin.success_add')], 200);
    }
}
