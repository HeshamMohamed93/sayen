<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ServiceRequest;
use App\Service;
use Auth;
use Illuminate\Routing\Route;
use App\ServiceHour;
use DB;

class ServiceController extends Controller
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

    function __construct(Request $request, Route $route, Service $service)
    {
        $this->prefix = 'services.';
        $this->model = $service;
        $this->upload_folder = 'services';
        $this->view_folder = 'admin.services.';
        $this->icon = 'fa fa-user-secret';
        
        $this->success_save = trans('admin.success_save');
        $this->redirect_url = route($this->prefix.'index'); 

        $current_method = $route->getActionMethod();

        if($current_method == 'index')
        {
            $this->page_title = trans('admin.services');
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
                        $query->where('name', 'like', '%' . $request->search . '%')
                            ->orWhere('initial_price', 'like', '%' . $request->search . '%');
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
                $services = $this->model::where('active',1)->pluck('name','id')->toArray();
                return view($this->view_folder.'form', compact('page_title', 'method', 'submit_action','services'));
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
    public function store(ServiceRequest $request)
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
                    'name' => $request->name,
                    'name_en' => $request->name_en,
                    'initial_price' => $request->initial_price,
                    'initial_price_excellence_client' => $request->initial_price_excellence_client,
                    'image' => $image,
                    'active' => $request->active,
                    'text' => $request->text,
                    'text_en' => $request->text_en,
                    'warranty' => $request->warranty,
                    'parent_id' => $request->parent_id
                ]);
                
                foreach($request->count_hour as $key => $countHour){
                   $addHour = new ServiceHour();
                   $addHour->service_id = $x->id;
                   $addHour->hour = $key;
                   $addHour->count = $countHour;
                   $addHour->save();
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
                $countHours = ServiceHour::where('service_id',$id)->pluck('count','hour')->toArray();
                $services = $this->model::where('id','!=',$id)->where('active',1)->pluck('name','id')->toArray();
                return view($this->view_folder.'form', compact('service', 'page_title','countHours','services'));
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
                $countHours = ServiceHour::where('service_id',$id)->pluck('count','hour')->toArray();
                $services = $this->model::where('id','!=',$id)->where('active',1)->pluck('name','id')->toArray();
                return view($this->view_folder.'form', compact('service', 'page_title', 'method', 'submit_action','countHours','services'));
            }
        }   
    }
    // === End function ===

    // === Confirm edit service ===
    public function update(ServiceRequest $request, $id)
    {
        if(Auth::user()->permissions)
        {
            if(Auth::user()->permissions->can_edit)
            {
                $service['name'] = $request->name;
                $service['name_en'] = $request->name_en;
                $service['initial_price'] = $request->initial_price;
                $service['initial_price_excellence_client'] = $request->initial_price_excellence_client;
                $service['active'] = $request->active;
                $service['warranty'] = $request->warranty;
                $service['number_admin'] = $request->number_admin;
                $service['number_user'] = $request->number_user;
                $service['parent_id'] = $request->parent_id;
                $service['text'] = $request->text;
                $service['text_en'] = $request->text_en;
                $service['device_number'] = $request->device_number;
                $service['numbers'] = ($request->device_number == 1)?$request->numbers:1;
                if($request->has('image'))
                {
                    $service['image'] = uploadImage($request->image, $this->upload_folder);
                }

                $this->model::where('id', $id)->update($service);
                ServiceHour::where('service_id',$id)->delete();
                foreach($request->count_hour as $key => $countHour){
                    $addHour = new ServiceHour();
                    $addHour->service_id = $id;
                    $addHour->hour = $key;
                    $addHour->count = $countHour;
                    $addHour->save();
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
}
