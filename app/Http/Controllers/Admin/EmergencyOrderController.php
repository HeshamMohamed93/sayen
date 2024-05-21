<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Routing\Route;
use App\Http\Requests\Admin\EmergencyOrderRequest;
use Auth;
use App\Team;
use App\Service;
use App\Order;
use App\EmergencyOrder;
use DB;
use Carbon\Carbon;
use App\User;
use App\PayOnlineTransaction;
use App\Refund;
use DateTime;
use App\OrderInvoice;
use App\TeamService;
use App\ReportProblem;
use App\EmergencyService;

class EmergencyOrderController extends Controller
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

    function __construct(Request $request, Route $route, EmergencyOrder $order)
    {
        $this->prefix = 'emergency-orders.';
        $this->model = $order;
        $this->upload_folder = 'Emergencyorders';
        $this->view_folder = 'admin.Emergencyorders.';
        $this->icon = 'fa fa-user-secret';

        $this->success_save = trans('admin.success_save');
        $this->redirect_url = route($this->prefix.'index');

        $current_method = $route->getActionMethod();

        if($current_method == 'index')
        {
            $this->page_title = trans('admin.emergency_orders');
        }
        else if($current_method == 'create')
        {
            $this->page_title = trans('admin.add_new_order');
            $this->submit_action = route($this->prefix.'store');
        }
        else if($current_method == 'store')
        {
            $this->submit_action = route($this->prefix.'store');
        }
        else if($current_method == 'show')
        {
            $this->page_title = trans('admin.show_order');
        }
        else if($current_method == 'edit')
        {
            $this->page_title = trans('admin.edit_order');
        }
        else if($current_method == 'update')
        {
            $this->page_title = trans('admin.edit_order');
        }
        else if($current_method == 'destroy')
        {
            $this->page_title = trans('admin.success_delete');
        }
        else if($current_method == 'cancel')
        {
            $this->page_title = trans('admin.success_cancel');
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
                
                $orders = (new EmergencyOrder)->newQuery();
                if($request->has('order_type')  && $request->order_type != 7 && $request->has('order_service'))
                {
                    if($request->order_type != 0 && $request->order_service != 0)
                    {
                        $orders->where('status', $request->order_type)->where('service_id', $request->order_service);
                    }
                    elseif($request->order_type == 0 && $request->order_service != 0)
                    {
                        $orders->where('service_id', $request->order_service);
                    }
                    elseif($request->order_type != 0 && $request->order_service == 0)
                    {
                        $orders->where('status', $request->order_type);
                    }
                }
                if($request->has('order_type') && $request->order_type == 7){
                    $orders->onlyTrashed();
                }
                if($request->has('order_client'))
                {
                    ($request->order_client != 0)?$orders->where('user_id', $request->order_client):'';
                }
                if($request->has('order_team'))
                {
                    ($request->order_team != 0)?$orders->where('team_id', $request->order_team):'';
                }

                if(isset($request->date_from) && $request->date_to == null)
                {
                    $orders->whereDate('created_at', '>=', $request->date_from);
                }
                else if(isset($request->date_to) && $request->date_from == null)
                {   
                    $orders->whereDate('created_at', '<', $request->date_to);
                }
                else if(isset($request->date_to) && isset($request->date_from))
                {
                    $orders->whereDate('created_at', '>=', $request->date_from)->whereDate('created_at', '<', $request->date_to);
                }

                $orders = $orders->when($request->search, function ($query) use ($request) {
                    $query->where(function($q) use ($request){
                        $q->where('order_number', 'like', '%' . $request->search . '%')
                        ->orWhere('created_at', 'like', '%' . $request->search . '%');
                    });
                })->orderBy('id', 'DESC')->get();
                EmergencyOrder::where('seen',0)->update(['seen'=>1]);
                $page_title = $this->page_title;
                $reportProblems = ReportProblem::pluck('problem','id')->toArray();
                $services = Service::pluck('name','id')->toArray();
                $teams = Team::pluck('name','id')->toArray();
                $users = User::select('name','id','phone')->get();
                return view($this->view_folder.'grid', compact('orders', 'page_title','reportProblems','services','teams','users'));
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
                
                $order = $this->model::find($id);
                $services = EmergencyService::withTrashed()->get();
                $order['visit_date'] = Carbon::createFromFormat('Y-m-d H:i:s', $order['created_at']);
                $page_title = $this->page_title;

                $start_time = Carbon::parse($order->team_start_at);
                $finish_time = Carbon::parse($order->team_end_at);
                $total_duration = $finish_time->diffInMinutes($start_time);//->format('%i');
                $serviceTeam = Team::all();
                if($total_duration > 59)
                {
                    $total_duration = $finish_time->diff($start_time)->format('%h');
                    $total_duration .= ' '.trans_choice('admin.hour', $total_duration);
                }
                else
                {
                    $total_duration .= ' '.trans_choice('admin.minute', $total_duration);
                }
                $reportProblems = ReportProblem::pluck('problem','id')->toArray();
                return view($this->view_folder.'form', compact('reportProblems','services', 'order', 'page_title', 'total_duration', 'serviceTeam'));
            }
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
                $services = EmergencyService::withTrashed()->get();
                $order = $this->model::find($id);
                $order['visit_date'] = Carbon::createFromFormat('Y-m-d H:i:s', $order['created_at']);
                $method = 'put';
                $submit_action = route($this->prefix.'update', $id);
                $page_title = $this->page_title;
                $serviceTeam = Team::all();
                $reportProblems = ReportProblem::pluck('problem','id')->toArray();
                return view($this->view_folder.'form', compact('reportProblems','services', 'order', 'page_title', 'submit_action', 'method', 'serviceTeam'));
            }
        }
    }
    // === End function ===

    // === Confirm edit service ===
    public function update(EmergencyOrderRequest $request, $id)
    {
        if(Auth::user()->permissions)
        {
            if(Auth::user()->permissions->can_edit)
            {
                $error = [];

                if($request->team_id == null && $request->status != 4)
                {
                    $error['not_found_team'] = trans('admin.no_assign_team');
                    return response()->json(['errors' => $error], 400);
                }
                else
                {
                    $team = Team::where([['id', $request->team_id], ['active', '1']])->first();

                    if(!$team && $request->team_id != null)
                    {
                        $error['not_found_team'] = trans('admin.not_available_team');
                    }

                    if(count($error) > 0)
                    {
                        return response()->json(['errors' => $error], 400);
                    }
                    else
                    {
                        $order = $this->model::where('id', $id)->first();

                        if($request->status == 4)   //=== cancel
                        {
                            $order->cancelled_by = '3';
                            $order->cancel_reason = $request->problem;
                            $order->cancelled_at = Carbon::now();
                        }
                        // 2 = current, 5 = assign to team via admin
                        else if($request->status == 2)  //=== current
                        {
                            $order->team_start_at = Carbon::now();
                        }
                        else if($request->status == 3)  //=== done
                        {
                            $order->team_end_at = Carbon::now();
                        }
                        //$request['created_at'] = Carbon::parse(date_format(date_create($request->visit_date),'Y-m-d H:i:s'));
                        //dd($request['created_at']['date']);
                        //$order_visit_date = Carbon::create($order->visit_date);
                        /*
                            if($request['visit_date'] < Carbon::now() && $request['visit_date']->format('Y-m-d H:i') != $order_visit_date->format('Y-m-d H:i'))
                            {
                                return response()->json(['errors' => ['visit_date_is_old' => trans('admin.visit_date_is_old')]], 400);
                            }
                        */

                        if($order->team_id == $request->team_id)
                        {
                            $team_notification['msg'] = trans('notification.team_edit_order_notification', ['value' => $order['order_number']]);
                            $team_notification['image'] = 'default_update.png';

                            $user_notification_msg = trans('notification.user_edit_order_notification', ['value' => $order['order_number']]);
                        }
                        else
                        {
                            $team_notification['msg'] = trans('notification.team_new_order_notification', ['value' => $order['order_number']]);
                            $team_notification['image'] = 'default_right.png';

                            $user_notification_msg = trans('notification.user_accept_order_notification', ['value' => $order['order_number']]);
                            $order->team_id = $request->team_id;
                        }
                        
                        $order->created_at = $request->visit_date.':00';
                        $order->admin_note = $request->admin_note;
                        $order->status = $request->status;
                        $order->text = $request->text;
                        $order->service_id = $request->service_id;
                        
                        $order->save();
                        
                        /*
                            $OrderInvoice = OrderInvoice::where('order_id',$order->id)->first();
                            if(isset($request->final_price)){
                                $OrderInvoice->final_price = $request->final_price;
                            }
                            if(isset($request->initial_price)){
                                $OrderInvoice->initial_price = $request->initial_price;
                            }
                            $OrderInvoice->save();
                        */
                        //$this->createOrderNotification($order, $team_notification, $user_notification_msg);
                        EmergencyOrder::where('id',$order->id)->where('seen',0)->update(['seen'=>1]);
                        $url = url("admin-panel/emergency-orders/$order->id/edit");
                        return response()->json(['redirect' => $url, 'message' => $this->success_save], 200);
                    }
                }
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
                $notification_data['message'] = trans('notification.team_close_order', ['value' => $order['order_number']]);
                createNotification($notification_data);
            break;

            case 4: //=== cancel
                $notification_data['user_id'] = $order['user_id'];
                $notification_data['user_type'] = '1';
                $notification_data['message'] = trans('notification.team_cancel_service_notification');
                createNotification($notification_data);

                if($order['team_id'] != null)
                {
                    $notification_data['user_id'] = $order['team_id'];
                    $notification_data['user_type'] = '2';
                    $notification_data['message'] = trans('notification.team_cancel_service_notification');
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

            if($order)
            {
                $notification['order_id'] = $order->id;
                $notification['user_id'] = $order->user_id;
                $notification['user_type'] = '1';
                $notification['message'] = trans('notification.admin_send_invoice', ['value' => $order->order_number]);
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

}
