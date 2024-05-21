<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use App\Order;
use DB;

class BankUpReportController extends Controller
{
    // === Report form ===
    public function index(Request $request)
    {
        if(Auth::user()->permissions)
        {
            if(Auth::user()->permissions->can_create)
            {
                $page_title = trans('admin.bankup_report');
                $method = 'get';
                $submit_action = route('bankup-report.index');
                $data = [];

                if(count($request->all()) > 0)
                {
                    $data = $this->generateReport($request->all(), 'grid');
                }

                return view('admin.bankup_report.form', compact('page_title', 'method', 'submit_action', 'data'));
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

        if($request['bankup_from'] == 1)  //=== from client
        {
            $sales->whereHas('orderInvoice', function($q){
                $q->where('pay_by', '1');
            });
        }
        else  //=== from owner
        {
            $sales->whereHas('orderInvoice', function($q){
                $q->where('pay_by', '2');
            });
        }

        $sales->where('status', '3')->orderBy('visit_date', 'DESC');

        if($type == 'grid')
        {
            return $data = $sales->paginate(10);
        }
        else
        {
            return $data = $sales->get();
        }
    }
    // === End function ===

    //=== Export report in csv file ===
    public function exportReport(Request $request)
    {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="report.csv"');

        $data = $this->generateReport($request->all(), 'export');
        $user_CSV[0] = array(trans('admin.order_number'), trans('admin.service'), trans('admin.bankup_from'), trans('admin.teams'), 
                                trans('admin.visit_date'), trans('admin.final_price'));
        foreach($data as $index => $row)
        {            
            if($row->orderService->pay_by == 1)
            {
                $pay_by = trans('admin.client');
            }
            else
            {
                $pay_by = trans('admin.owner');
            }

            $user_CSV[$index+1] = array($row->order_number, $row->orderService->name, $pay_by, $row->orderTeam->name, 
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
