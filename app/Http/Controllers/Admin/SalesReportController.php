<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Service;
use Auth;
use Carbon\Carbon;
use App\Order;
use DB;

class SalesReportController extends Controller
{
    // === Report form ===
    public function index(Request $request)
    {
        if(Auth::user()->permissions)
        {
            if(Auth::user()->permissions->can_create)
            {
                $services = Service::get();
                $page_title = trans('admin.sales_report');
                $method = 'get';
                $submit_action = route('sales-report.index');
                $data = [];

                if(count($request->all()) > 0)
                {
                    $data = $this->generateReport($request->all(), 'grid');
                }

                return view('admin.sales_report.form', compact('page_title', 'method', 'submit_action', 'services', 'data'));
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
        $sales = (new Order)->newQuery();
        
        if(isset($request['service']) && !in_array(0,$request['service']))
        {
            $sales->whereIN('service_id', $request['service']);
        }

        if($request['type'] == 1)
        {
            if(isset($request['date_from']) && $request['date_to'] == null)
            {
                $sales->whereDate('team_end_at', '>=', $request['date_from']);
            }
            else if(isset($request['date_to']) && $request['date_from'] == null)
            {   
                $sales->whereDate('team_end_at', '<', $request['date_to']);
            }
            else if(isset($request['date_to']) && isset($request['date_from']))
            {
                $sales->whereDate('team_end_at', '>=', $request['date_from'])->whereDate('team_end_at', '<', $request['date_to']);
            }
        }
        else if($request['type'] == 2)  //=== day
        {
            $sales->whereDate('team_end_at', $request['date_from']);
        }
        else if($request['type'] == 3)  //=== week
        {
            if($request['date_from'] == null){
                return $data = [];
            }else{
                
                $end_week = Carbon::create($request['date_from']);
                $dateFrom = $end_week->format('Y-m-d');
                $endWeak = $end_week->addDays(7)->format('Y-m-d');
                $sales->whereDate('team_end_at', '>=', $dateFrom)->whereDate('team_end_at', '<=', $endWeak);
                //$sales->wherebetween('team_end_at',[$dateFrom,$endWeak]);
            }
        }
        else if($request['type'] == 4)  //=== month
        {
            $sales->whereMonth('team_end_at', $request['month']);
        }
        
        $sales->where('status', '3')->orderBy('visit_date', 'DESC');
        
        if($type == 'grid')
        {
            return $data = $sales->paginate(500);
        }
        else
        {
            return $data = $sales->paginate(500);
        }
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
}
