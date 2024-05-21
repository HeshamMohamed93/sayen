<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Routing\Route;
use Auth;
use App\ReportProblem;
use App\Http\Requests\Admin\ReportProblemRequest;

class ReportProblemController extends Controller
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

    function __construct(Request $request, Route $route, ReportProblem $report_problem)
    {
        
        $this->prefix = 'report-problems.';
        $this->model = $report_problem;
        $this->view_folder = 'admin.report_problems.';
        $this->icon = 'fa fa-user-secret';
        
        $this->success_save = trans('admin.success_save');
        $this->redirect_url = route($this->prefix.'index'); 
        
        $current_method = $route->getActionMethod();

        if($current_method == 'index')
        {
            $this->page_title = trans('admin.report_problems');
        }
        else if($current_method == 'create')
        {
            $this->page_title = trans('admin.add_new_report_problem');
            $this->submit_action = route($this->prefix.'store'); 
        }
        else if($current_method == 'store')
        {
            $this->submit_action = route($this->prefix.'store'); 
        }
        else if($current_method == 'show')
        {
            $this->page_title = trans('admin.show_report_problem');
        }
        else if($current_method == 'edit')
        {
            $this->page_title = trans('admin.edit_report_problem');
        }
        else if($current_method == 'destroy')
        {
            $this->page_title = trans('admin.success_delete');
        }
    }

    // === Get all report problems ===
    public function index(Request $request)
    {
        
        if(Auth::user()->permissions)
        {
            $report_problems = $this->model::when($request->search, function ($query) use ($request) {
                        $query->where('problem', 'like', '%' . $request->search . '%');
            })->orderBy('id', 'DESC')->paginate(10);

            $page_title = $this->page_title;
            
            return view($this->view_folder.'grid', compact('report_problems', 'page_title'));
        }
        else
        {
            return redirect()->route('home'); 
        }
    }
    // === End function ===

    // === Create new report problem page ===
    public function create()
    {
        if(Auth::user()->permissions)
        {
            if(Auth::user()->permissions->can_create)
            {
                $page_title = $this->page_title;
                $method = 'post';
                $submit_action = $this->submit_action; 
                return view($this->view_folder.'form', compact('page_title', 'method', 'submit_action'));
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

    // === Save new report problem ===
    public function store(ReportProblemRequest $request)
    {
        if(Auth::user()->permissions)
        {
            if(Auth::user()->permissions->can_create)
            {
                $this->model::create([
                    'problem' => $request->desc_problem,
                    'problem_en' => $request->desc_problem_en,
                ]);
                
                return response()->json(['redirect' => $this->redirect_url, 'message' => $this->success_save], 200);
            }
        }     
    }
    // === End function ===

    // === Show report problem ===
    public function show($id)
    {
        if(Auth::user()->permissions)
        {
            if(Auth::user()->permissions->can_show)
            {
                $report_problem = $this->model::find($id);
                $page_title = $this->page_title;
                return view($this->view_folder.'form', compact('report_problem', 'page_title'));
            }
        }   
    }
    // === End function ===

    // === Edit existing report problem ===
    public function edit($id)
    {
        if(Auth::user()->permissions)
        {
            if(Auth::user()->permissions->can_edit)
            {
                $report_problem = $this->model::find($id);
                $method = 'put';
                $submit_action = route($this->prefix.'update', $id);
                $page_title = $this->page_title;

                return view($this->view_folder.'form', compact('report_problem', 'page_title', 'method', 'submit_action'));
            }
        }   
    }
    // === End function ===

    // === Confirm edit report problem ===
    public function update(ReportProblemRequest $request, $id)
    {
        if(Auth::user()->permissions)
        {
            if(Auth::user()->permissions->can_edit)
            {
                $report_problem['problem'] = $request->desc_problem;
                $report_problem['problem_en'] = $request->desc_problem_en;

                $this->model::where('id', $id)->update($report_problem);
                
                return response()->json(['redirect' => $this->redirect_url, 'message' => $this->success_save], 200);
            }
        }     
    }
    // === End function ===

    // === Delete report problem ===
    public function destroy($id)
    {
        if(Auth::user()->permissions)
        {
            if(Auth::user()->permissions->can_delete)
            {
                $report_problem = $this->model::find($id);
                $report_problem->delete();
                return response()->json(['redirect' => $this->redirect_url, 'message' => trans('admin.success_delete')], 200);  
            }
        }
    }
    // === End function ===
}