<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EmergencyServiceRequest;
use App\EmergencyService;
use Auth;
use Illuminate\Routing\Route;
use DB;
use App\EmergencyServiceReason;

class EmergencyServiceController extends Controller
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

    function __construct(Request $request, Route $route, EmergencyService $service)
    {
        $this->prefix = 'emergency-services.';
        $this->model = $service;
        $this->upload_folder = 'emergencyServices';
        $this->view_folder = 'admin.emergencyServices.';
        $this->icon = 'fa fa-user-secret';
        
        $this->success_save = trans('admin.success_save');
        $this->redirect_url = route($this->prefix.'index'); 

        $current_method = $route->getActionMethod();

        if($current_method == 'index')
        {
            $this->page_title = trans('admin.emergency_services');
        }
        else if($current_method == 'create')
        {
            $this->page_title = trans('admin.add_new_service');
            $this->submit_action = route($this->prefix.'store'); 
        }
        else if($current_method == 'store')
        {
            $this->submit_action = route($this->prefix.'store'); 
        }
        else if($current_method == 'show')
        {
            $this->page_title = trans('admin.show_service');
        }
        else if($current_method == 'edit')
        {
            $this->page_title = trans('admin.edit_service');
        }
        else if($current_method == 'update')
        {
            $this->page_title = trans('admin.edit_admin');
        }
        else if($current_method == 'destroy')
        {
            $this->page_title = trans('admin.success_delete');
        }
    }

    // === Get all services ===
    public function index(Request $request)
    {
        if(Auth::user()->permissions)
        {
            $services = $this->model::when($request->search, function ($query) use ($request) {
                        $query->where('title', 'like', '%' . $request->search . '%')
                            ->orWhere('title_en', 'like', '%' . $request->search . '%');
            })->orderBy('id', 'DESC')->paginate(10);
            $page_title = $this->page_title;
            return view($this->view_folder.'grid', compact('services', 'page_title'));
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

    // === Save new service ===
    public function store(EmergencyServiceRequest $request)
    {
        if(Auth::user()->permissions)
        {
            if(Auth::user()->permissions->can_create)
            {
                $image = '';
                
                if($request->has('image'))
                {
                    $image = uploadImage($request->image, $this->upload_folder);
                }

                $x = $this->model::create([
                    'title' => $request->title,
                    'title_en' => $request->title_en,
                    'status' => $request->status
                ]);

                if(isset($request->reason)){
                    $reasons = $request->reason;
                    foreach($reasons as $key => $reason){
                        $add = new EmergencyServiceReason();
                        $add->reason = $reason;
                        $add->service_id = $x->id;
                        $add->reason_en = $request->reason_en[$key];
                        $add->status = $request->status[$key];
                        $add->save();
                    }
                }else{
                    $xx = $this->model::find($x->id);
                    $xx->status = 0;
                    $xx->save();
                }
                
                return response()->json(['redirect' => $this->redirect_url, 'message' => $this->success_save], 200);
            }
        }     
    }
    // === End function ===

    // === Show service ===
    public function show($id)
    {
        if(Auth::user()->permissions)
        {
            if(Auth::user()->permissions->can_show)
            {
                $service = $this->model::find($id);
                $page_title = $this->page_title;
                return view($this->view_folder.'form', compact('service', 'page_title'));
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
                $service = $this->model::find($id);
                $method = 'put';
                $submit_action = route($this->prefix.'update', $id);
                $page_title = $this->page_title;
                $reasons = EmergencyServiceReason::where('status',1)->where('service_id',$id)->get();
                return view($this->view_folder.'form', compact('service', 'page_title', 'method', 'submit_action','reasons'));
            }
        }   
    }
    // === End function ===

    // === Confirm edit service ===
    public function update(EmergencyServiceRequest $request, $id)
    {
        if(Auth::user()->permissions)
        {
            if(Auth::user()->permissions->can_edit)
            {
                $service['title'] = $request->title;
                $service['title_en'] = $request->title_en;
                $service['status'] = $request->status;
                if($request->has('image'))
                {
                    $service['image'] = uploadImage($request->image, $this->upload_folder);
                }

                $this->model::where('id', $id)->update($service);
                EmergencyServiceReason::where('service_id',$id)->delete();
                if(isset($request->reason)){
                    $reasons = $request->reason;
                    foreach($reasons as $key => $reason){
                        $add = new EmergencyServiceReason();
                        $add->reason = $reason;
                        $add->service_id = $id;
                        $add->reason_en = $request->reason_en[$key];
                        $add->status = $request->status_reason[$key];
                        $add->save();
                    }
                }else{
                    $xx = $this->model::find($id);
                    $xx->status = 0;
                    $xx->save();
                }
                return response()->json(['redirect' => $this->redirect_url, 'message' => $this->success_save], 200);
            }
        }     
    }
    // === End function ===

    // === Delete Service ===
    public function destroy($id)
    {
        if(Auth::user()->permissions)
        {
            if(Auth::user()->permissions->can_delete)
            {
                $service = $this->model::find($id);
                $service->delete();
                return response()->json(['redirect' => $this->redirect_url, 'message' => trans('admin.success_delete')], 200);  
            }
        }
    }
    // === End function ===
    public function serviceStop(){
        DB::table('service_hours')->update(['count'=>0]);
        return response()->json(['redirect' => $this->redirect_url, 'message' => trans('admin.success_stop')], 200);
    }
    public function addNewReason(){
        dd('sadasd');
    }
}
