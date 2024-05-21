<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use App\Order;
use App\Building;
use DB;
use App\User;
use App\Service;
use App\EmergencyOrder;

class MaintenanceReportController extends Controller
{
    // === Report form ===
    public function index(Request $request)
    {
        if(Auth::user()->permissions)
        {
            if(Auth::user()->permissions->can_create)
            {
                $buildings = Building::withTrashed()->get();
                $users = User::withTrashed()->get();
                $services = Service::where('active',1)->where('parent_id',0)->get();
                $page_title = trans('admin.maintenance_report');
                $method = 'get';
                $submit_action = route('maintenance-report.index');
                $data = [];
                if(count($request->all()) > 0)
                {
                    $data = $this->generateReport($request->all(), 'grid');
                }

                return view('admin.maintenance_report.form', compact('services','page_title', 'method', 'submit_action', 'data', 'buildings', 'users'));
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

    // === Generate report data ===
    private function generateReport($request, $type)
    {
        $sales1 = (new Order)->get();
        $sales2 = (new EmergencyOrder)->get();
        //dd($sales1,$sales2);
        foreach($sales1 as $salesOrder){
            $salesOrder['type_order'] = 'order_normal';
        }
        foreach($sales2 as $salesEmergencyOrder){
            $salesEmergencyOrder['type_order'] = 'emergency_order';
        }

        if(isset($request['service']) && !in_array(0,$request['service']))
        {
            $parentIDS = Service::whereIN('parent_id',$request['service'])->pluck('id')->toArray();
            if($parentIDS){
                foreach($request['service'] as $serOne){
                    array_push($parentIDS,$serOne);
                }
                $sales = $sales1->merge($sales2)->whereIN('service_id', $parentIDS)->where('status', '3');
            }else{
                $sales = $sales1->merge($sales2)->whereIN('service_id', $request['service'])->where('status', '3');
            }

            if($request['type'] == 1 && $request['user_id'] != 0)  //=== from user
            {
                $request['building_id'] = '';
                $sales = $sales->where('user_id', $request['user_id'])->where('status', '3');
            }
            elseif($request['type'] == 2 && $request['building_id'] != 0)  //=== from building
            {
                $request['user_id'] = '';
                $sales = $sales->whereHas('orderUser', function($q) use ($request){
                    $q->where('building_id', $request['building_id']);
                })->where('status', '3');
            }
        }else{
            if($request['type'] == 1 && $request['user_id'] != 0)  //=== from user
            {
                $request['building_id'] = '';
                $sales = $sales1->merge($sales2)->where('user_id', $request['user_id'])->where('status', '3');
            }
            elseif($request['type'] == 2 && $request['building_id'] != 0)  //=== from building
            {
                $request['user_id'] = '';
                $sales = $sales1->merge($sales2);
                $salesUsers1 = (new Order)->pluck('user_id')->toArray();
                $salesUsers2 = (new EmergencyOrder)->pluck('user_id')->toArray();
                $salesUsers = array_merge($salesUsers1,$salesUsers2);
                $users = User::whereIN('id',$salesUsers)->where('building_id',$request['building_id'])->pluck('id')->toArray();
                $sales = $sales->whereIN('user_id',$users)->where('status', '3');
            }else{
                $sales = $sales1->merge($sales2)->where('status', '3');
            }
            
        }

        //$sales->where('status', '3');
        
        //dd($request['maintence-report-from'],$request['maintence-report-to']);
        // dd($sales,$request['maintence-report-from'],$request['maintence-report-to']);
        $from = (isset($request['maintence-report-from']))?date('Y-m-d', strtotime($request['maintence-report-from'] . ' -1 day')):null;
        $to  = (isset($request['maintence-report-to']))?date('Y-m-d', strtotime($request['maintence-report-to'] . ' +1 day')):null;

        if(isset($request['maintence-report-from']) && $request['maintence-report-to'] == null)
        {
            $sales = $sales->where('team_end_at', '>=', $from.' 23:59:59')->where('team_end_at','!=','')->sortBy('created_at')->all();
        }
        else if(isset($to) && $from == null)
        {   
            $sales = $sales->where('team_end_at', '<', $to.' 23:59:59')->where('team_end_at','!=','')->sortBy('created_at')->all();
        }
        else if(isset($request['maintence-report-to']) && isset($request['maintence-report-from']))
        {
            $from = (isset($request['maintence-report-from']))?date('Y-m-d', strtotime($request['maintence-report-from'])):null;
            $to  = (isset($request['maintence-report-to']))?date('Y-m-d', strtotime($request['maintence-report-to'] . ' +1 day')):null;
            $sales = $sales->whereBetween('team_end_at',[$from,$to])->where('team_end_at','!=','')->sortBy('created_at')->all();
        }
        
        // if($type == 'grid')
        // {
        //     return $data = $sales->sortBy('created_at');
        // }
        // else
        // {
        //     return $data = $sales->sortBy('created_at');
        // }
        return $sales;
    }
    // === End function ===

    //=== Export report in csv file ===
    public function exportReport(Request $request)
    {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="report.csv"');

        $data = $this->generateReport($request->all(), 'export');
        $user_CSV[0] = array(trans('admin.order_number'), trans('admin.service'), trans('admin.teams'), 
                                trans('admin.visit_date'), trans('admin.final_price'));
        foreach($data as $index => $row)
        {            
            $user_CSV[$index+1] = array($row->order_number, $row->orderService->name, $row->orderTeam->name, 
                                        $row->visit_date, $row->orderInvoice->final_price.' '.trans('admin.currency'));
        }
        
        $fp = fopen('php://output', 'wb');
        
        foreach ($user_CSV as $line) 
        {
            fputcsv($fp, $line, ',');
        }

        fclose($fp);
    }
    //=== End Function ===
    public function editReport($type,$id){
        
        if($type == 'order_normal'){
            $order = Order::find($id);
            $type = 'order';
        }else{
            $order = EmergencyOrder::find($id);
            $type = 'emergencyorder';
        }
        $manitenanceReport = DB::table('maintenanance_report')->where('type',$type)->where('order_id',$order->id)->first();
        $images = DB::table('images_maintenanance_report')->get();
        return view('admin.maintenance_report.editReport',compact('order','type','manitenanceReport','images'));
    }
}