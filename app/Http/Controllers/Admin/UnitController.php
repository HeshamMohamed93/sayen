<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UnitRequest;
use App\Building;
use App\Unit;
use Auth;
use Illuminate\Routing\Route;

class UnitController extends Controller
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

    function __construct(Request $request, Route $route, Unit $service)
    {
        $this->prefix = 'units.';
        $this->model = $service;
        $this->upload_folder = 'units';
        $this->view_folder = 'admin.units.';
        $this->icon = 'fa fa-building';

        $this->success_save = trans('admin.success_save');
        $this->redirect_url = route($this->prefix.'index');

        $current_method = $route->getActionMethod();

        if($current_method == 'index')
        {
            $this->page_title = trans('admin.units');
        }
        else if($current_method == 'create')
        {
            $this->page_title = trans('admin.add_new_unit');
            $this->submit_action = route($this->prefix.'store');
        }
        else if($current_method == 'store')
        {
            $this->submit_action = route($this->prefix.'store');
        }
        else if($current_method == 'show')
        {
            $this->page_title = trans('admin.show_unit');
        }
        else if($current_method == 'edit')
        {
            $this->page_title = trans('admin.edit_unit');
        }
        else if($current_method == 'destroy')
        {
            $this->page_title = trans('admin.success_delete');
        }
    }

    // === Get all units ===
    public function index(Request $request)
    {
        if(Auth::user()->permissions)
        {
            $units = (new Unit)->newQuery();
            if($request->has('building'))
            {
                ($request->building != 0)?$units->where('building_id', $request->building):'';
            }
            $units = $units->when($request->search, function ($query) use ($request) {
                        $query->where('units.name', 'like', '%' . $request->search . '%');
            })
            ->join('buildings', 'buildings.id', '=', 'units.building_id')
            ->select('units.*', 'buildings.name as building')
            ->orderBy('units.id', 'DESC')->paginate(10);

            $page_title = $this->page_title;
            $buildings = Building::pluck('name','id')->toArray();
            return view($this->view_folder.'grid', compact('units', 'page_title','buildings'));
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
                $buildings = Building::get();
                return view($this->view_folder.'form', compact('page_title', 'method', 'submit_action', 'buildings'));
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
    public function store(UnitRequest $request)
    {
        if(Auth::user()->permissions)
        {
            if(Auth::user()->permissions->can_create)
            {

                $this->model::create([
                    'name' => $request->name,
                    'building_id' => $request->building_id,
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
                $unit = $this->model::find($id);
                $page_title = $this->page_title;
                $buildings = Building::get();
                return view($this->view_folder.'form', compact('unit', 'page_title', 'buildings'));
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
                $unit = $this->model::find($id);
                $method = 'put';
                $submit_action = route($this->prefix.'update', $id);
                $page_title = $this->page_title;
                $buildings = Building::get();
                return view($this->view_folder.'form', compact('unit', 'page_title', 'method', 'submit_action', 'buildings'));
            }
        }
    }
    // === End function ===

    // === Confirm edit service ===
    public function update(UnitRequest $request, $id)
    {
        if(Auth::user()->permissions)
        {
            if(Auth::user()->permissions->can_edit)
            {
                $service['name'] = $request->name;
                $service['building_id'] = $request->building_id;
                $service['notes'] = $request->notes;

                $this->model::where('id', $id)->update($service);
                return response()->json(['redirect' => $this->redirect_url, 'message' => $this->success_save], 200);
            }
        }
    }
    // === End function ===

    // === Delete Unit ===
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
