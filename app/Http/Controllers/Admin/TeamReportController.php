<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use App\Team;
use DB;
use App\Service;
use App\TeamService;
use App\Order;

class TeamReportController extends Controller
{
    // === Report form ===
    public function index(Request $request)
    {
        if(Auth::user()->permissions)
        {
            if(Auth::user()->permissions->can_create)
            {
                $services = Service::withTrashed()->get();
                $page_title = trans('admin.team_report');
                $method = 'get';
                $submit_action = route('team-report.index');
                $data = [];

                if(count($request->all()) > 0)
                {
                    $data = $this->generateReport($request->all(), 'grid');
                }

                return view('admin.team_report.form', compact('page_title', 'method', 'submit_action', 'data', 'services'));
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
        $team = (new Team)->newQuery();

        if(isset($request['service_id']) && !in_array(0,$request['service_id'])) //=== all services
        {
            $teamIDS = TeamService::whereIN('service_id',$request['service_id'])->pluck('team_id')->toArray();
            $team->whereIN('id', $teamIDS);
        }
        if(isset($request['team-report-from']) && $request['team-report-to'] == null)
        {
            $teamIDS = Order::whereDate('visit_date', '>=', $request['team-report-from'])->pluck('team_id')->toArray();
            $team->whereIN('id', $teamIDS);
        }
        else if(isset($request['team-report-to']) && $request['team-report-from'] == null)
        {   
            $teamIDS = Order::whereDate('visit_date', '<', $request['team-report-to'])->pluck('team_id')->toArray();
            $team->whereIN('id', $teamIDS);
        }
        else if(isset($request['team-report-to']) && isset($request['team-report-from']))
        {
            $teamIDS = Order::whereDate('visit_date', '>=', $request['team-report-from'])->whereDate('visit_date', '<=', $request['team-report-to'])->pluck('team_id')->toArray();
            //$teamIDS = Order::wherebetween('visit_date',[$request['team-report-from'],$request['team-report-to']])->pluck('team_id')->toArray();
            $team->whereIN('id', $teamIDS);
        }
        else
        {
            $team->get();
        }
        if($type == 'grid')
        {
            $data = $team->paginate(500);
            foreach($data as $d){
                 $d['fromDate'] = isset($request['team-report-from']) ? $request['team-report-from']:'';
                 $d['toDate'] = isset($request['team-report-to']) ? $request['team-report-to']:'';
            }
            return $data;
        }
        else
        {
            $data = $team->paginate(500);
            foreach($data as $d){
                $d['fromDate'] = $request['team-report-from'];
                $d['toDate'] = $request['team-report-to'];
            }
            return $data;
        }
    }
    // === End function ===

    //=== Export report in csv file ===
    public function exportReport(Request $request)
    {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="report.csv"');

        $data = $this->generateReport($request->all(), 'export');
        $user_CSV[0] = array(trans('admin.name'), trans('admin.service'), trans('admin.total_orders'), trans('admin.working_hours'));
        foreach($data as $index => $row)
        {            
            $user_CSV[$index+1] = array($row->name, $row->teamService->name, $row->teamOrders(), $row->workingHours());
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