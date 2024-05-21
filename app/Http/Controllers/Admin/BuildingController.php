<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BuildingRequest;
use App\Building;
use Auth;
use Illuminate\Routing\Route;

class BuildingController extends Controller
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

    function __construct(Request $request, Route $route, Building $service)
    {
        $this->prefix = 'buildings.';
        $this->model = $service;
        $this->upload_folder = 'buildings';
        $this->view_folder = 'admin.buildings.';
        $this->icon = 'fa fa-building';
        
        $this->success_save = trans('admin.success_save');
        $this->redirect_url = route($this->prefix.'index'); 

        $current_method = $route->getActionMethod();

        if($current_method == 'index')
        {
            $this->page_title = trans('admin.buildings');
        }
        else if($current_method == 'create')
        {
            $this->page_title = trans('admin.add_new_building');
            $this->submit_action = route($this->prefix.'store'); 
        }
        else if($current_method == 'store')
        {
            $this->submit_action = route($this->prefix.'store'); 
        }
        else if($current_method == 'show')
        {
            $this->page_title = trans('admin.show_building');
        }
        else if($current_method == 'edit')
        {
            $this->page_title = trans('admin.edit_building');
        }
        else if($current_method == 'destroy')
        {
            $this->page_title = trans('admin.success_delete');
        }
    }

    // === Get all buildings ===
    public function index(Request $request)
    {
        if(Auth::user()->permissions)
        {
            $buildings = $this->model::when($request->search, function ($query) use ($request) {
                        $query->where('name', 'like', '%' . $request->search . '%');
            })->orderBy('id', 'DESC')->paginate(10);

            $page_title = $this->page_title;

            return view($this->view_folder.'grid', compact('buildings', 'page_title'));
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
    public function store(BuildingRequest $request)
    {
        if(Auth::user()->permissions)
        {
            if(Auth::user()->permissions->can_create)
            {

                $this->model::create([
                    'name' => $request->name,
                    'owner_name' => $request->owner_name,
                    'address' => $request->address,
                    'discount' => $request->discount,
                    'notes' => $request->notes
                ]);
                
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
                $building = $this->model::find($id);
                $page_title = $this->page_title;
                return view($this->view_folder.'form', compact('building', 'page_title'));
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
                $building = $this->model::find($id);
                $method = 'put';
                $submit_action = route($this->prefix.'update', $id);
                $page_title = $this->page_title;

                return view($this->view_folder.'form', compact('building', 'page_title', 'method', 'submit_action'));
            }
        }   
    }
    // === End function ===

    // === Confirm edit service ===
    public function update(BuildingRequest $request, $id)
    {
        if(Auth::user()->permissions)
        {
            if(Auth::user()->permissions->can_edit)
            {
                $service['name'] = $request->name;
                $service['owner_name'] = $request->owner_name;
                $service['address'] = $request->address;
                $service['discount'] = $request->discount;
                $service['notes'] = $request->notes;

                $this->model::where('id', $id)->update($service);
                return response()->json(['redirect' => $this->redirect_url, 'message' => $this->success_save], 200);
            }
        }     
    }
    // === End function ===

    // === Delete Building ===
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
}
