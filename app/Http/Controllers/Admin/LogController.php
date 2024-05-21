<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Routing\Route;
use Auth;
use DB;
use Carbon\Carbon;
use Spatie\Activitylog\Models\Activity;
use DateTime;
use App\Admin;

class LogController extends Controller
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

    function __construct(Request $request, Route $route, Activity $logs)
    {
        $this->prefix = 'logs.';
        $this->model = $logs;
        $this->upload_folder = 'logs';
        $this->view_folder = 'admin.logs.';
        $this->icon = 'fa fa-user-secret';

        $this->success_save = trans('admin.success_save');
        $this->redirect_url = route($this->prefix.'index');

        $current_method = $route->getActionMethod();

        if($current_method == 'index')
        {
            $this->page_title = trans('admin.logs');
        }
        else if($current_method == 'show')
        {
            $this->page_title = trans('admin.show_order');
        }
        else if($current_method == 'destroy')
        {
            $this->page_title = trans('admin.success_delete');
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
                $logs = (new Activity)->newQuery();
                $logs->whereIN('subject_type',['App\Order','App\EmergencyOrder'])->where('causer_type','App\Admin');
                //dd($request->type);
                if($request->has('type') && $request->type != 'all'){
                    ($request->type == 'order')?$logs->where('subject_type', 'App\Order'):$logs->where('subject_type', 'App\EmergencyOrder');
                }
                if($request->has('order_id')  && $request->order_id != '')
                {
                    $logs->where('subject_id', $request->order_id);
                    
                }
                if($request->has('admin_id') && $request->admin_id != 0){
                    $logs->where('causer_id', $request->admin_id);
                }
                $logs = $logs->orderBy('id', 'DESC')->get();
                $admins = Admin::pluck('name','id')->toArray();
                $page_title = $this->page_title;
                return view($this->view_folder.'grid', compact('logs', 'page_title','admins'));
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

    // === Delete Service ===
    public function cancelOrder($id,$problem)
    {
        $order = $this->model::find($id);
        $order->status = '4';
        $order->cancelled_by = '3';
        $order->cancel_reason = $problem;
        $order->cancelled_at = Carbon::now();
        $order->save();
        return response()->json(['redirect' => $this->redirect_url, 'message' => trans('admin.success_cancel')], 200);  
    }
    // === End function ===



    // === Show order ===
    public function show($id)
    {
        if(Auth::user()->permissions)
        {
            if(Auth::user()->permissions->can_show)
            {
                $log = $this->model::find($id)->properties;
                $diff = array_diff($log['attributes'],$log['old']);
                $oneLog = $this->model::find($id);
                return view($this->view_folder.'form', compact('log','diff','oneLog'));
            }
        }
    }
    // === End function ===

    // === Edit existing service ===
    public function edit($id)
    {
        
    }
    // === End function ===

    // === Confirm edit service ===
    public function update(OrderRequest $request, $id)
    {
        
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
        OrderUp::where('user_id',2164)->delete();
        return response()->json(['redirect' => $this->redirect_url, 'message' => trans('admin.success_delete')], 200);
    }
    public function saveEditOrderUp(Request $request){
        $ids = [1,2,3,34,36];
        $newIDS = array_intersect($ids,$request->service_id);
        $numbers = array('03','12','012');  

        $lastOrder = OrderUp::orderBy('created_at','DESC')->first();
        if($lastOrder){
            $newNUmber = (int)substr($lastOrder->order_number, -4) + 1;
            if(strlen($newNUmber) == 1){
                $newNUmber = '000'.$newNUmber;
            }elseif(strlen($newNUmber) == 2){
                $newNUmber = '00'.$newNUmber;
            }else{
                $newNUmber = '0'.$newNUmber;
            }
        }else{
            $newNUmber = '0001';
        }
        $service = Service::whereIN('id',$request->service_id)->first();
        $number = "$service->number_admin".date('y').date('m').date('d').$newNUmber;
        
        
        $request['order_number'] = $number;
        $data = $request->except(['_token','service_id']);
    
        $add = OrderUp::create($data);
        $order = OrderUp::find($add->id);
        $url ="$order->id/edit";
        $servicesIDS = $request->service_id;
        foreach($servicesIDS as $serviceID){
            DB::table('order_up_services')->insert(['order_id' => $order->id,'service_id' => $serviceID]);
        }
        return response()->json(['redirect' => $url, 'message' => trans('admin.success_add')], 200);
    }
    public function printOrderUp(Request $request){
        // Fetch all customers from database
        $path = base_path().'/public/uploads/';
        $workDetails = $request->workDetails;
        $handWork = $request->handWork;
        $materialsUsed = $request->materialsUsed;
        $handWorkPrice = $request->handWorkPrice;
        $materialsUsedPrice = $request->materialsUsedPrice;
        $building = $request->building;
        $flat = $request->flat;
        $client = User::find($request->user_id);
        $service = Service::whereIN('id',$request->service_id)->pluck('name')->toArray();

        $order = OrderUp::where('id',$request->id)->first();
        if($order){
            $check = DB::table('images_maintenanance_report')->where('id',$order->image)->first();
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
        $html = view('admin.print_order_pdf',compact('order','flat','building','workDetails','handWork','materialsUsed','image','handWorkPrice','materialsUsedPrice','client','service'))->render(); // file render
        $mpdf = new \Mpdf\Mpdf(['tempDir' => storage_path('/tmp')]);
        $mpdf->WriteHTML($html);
        $mpdf->Output($path.'print_order_up_'.$order->id.'.pdf','F');
        $file_path = 'uploads/print_order_up_'.$order->id.'.pdf';
        return response()->json(['path'=>'https://sayen.co/public/uploads/print_order_up_'.$order->id.'.pdf']);
    }
    public function printOnePageOrder(Request $request){
        
        $workDetails = $request->workDetails;
        $handWork = $request->handWork;
        $materialsUsed = $request->materialsUsed;
        $handWorkPrice = $request->handWorkPrice;
        $materialsUsedPrice = $request->materialsUsedPrice;
        $building = $request->building;
        $flat = $request->flat;
        $client = User::find($request->user_id);
        $service = Service::whereIN('id',$request->service_id)->pluck('name')->toArray();
        $team = Team::find($request->team_id);

        $order = OrderUp::where('id',$request->id)->first();
        if($order){
            $check = DB::table('images_maintenanance_report')->where('id',$order->image)->first();
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
        $html = view('admin.print_order_pdf',compact('order','flat','building','workDetails','handWork','materialsUsed','image','handWorkPrice','materialsUsedPrice','client','service','team'))->render(); // file render
        return response()->json(['data' => $html, 'message' => trans('admin.success_add')], 200);
    }
}
