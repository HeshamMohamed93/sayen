<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Routing\Route;
use App\Http\Requests\Admin\TeamRequest;
use App\Team;
use Auth;
use App\Service;
use App\TeamService;

class TeamController extends Controller
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

    function __construct(Request $request, Route $route, Team $team)
    {
        $this->prefix = 'teams.';
        $this->model = $team;
        $this->upload_folder = 'teams';
        $this->view_folder = 'admin.teams.';
        $this->icon = 'fa fa-user-secret';
        
        $this->success_save = trans('admin.success_save');
        $this->redirect_url = route($this->prefix.'index'); 

        $current_method = $route->getActionMethod();

        if($current_method == 'index')
        {
            $this->page_title = trans('admin.teams');
        }
        else if($current_method == 'create')
        {
            $this->page_title = trans('admin.add_new_team');
            $this->submit_action = route($this->prefix.'store'); 
        }
        else if($current_method == 'store')
        {
            $this->submit_action = route($this->prefix.'store'); 
        }
        else if($current_method == 'show')
        {
            $this->page_title = trans('admin.show_team');
        }
        else if($current_method == 'edit')
        {
            $this->page_title = trans('admin.edit_team');
        }
        else if($current_method == 'update')
        {
            $this->page_title = trans('admin.edit_team');
        }
        else if($current_method == 'destroy')
        {
            $this->page_title = trans('admin.success_delete');
        }
    }   

    // === Get all teams ===
    public function index(Request $request)
    {
        if(Auth::user()->permissions)
        {
            $teams = (new Team)->newQuery();
            
            if($request->has('status'))
            {
                if($request->status != -1)
                {
                    $teams->where('active', $request->status);
                }
                else
                {
                    return redirect()->route('teams.index');
                }
            }

            $teams = $teams->when($request->search, function ($query) use ($request) {
                        $query->where('name', 'like', '%' . $request->search . '%')
                            ->orWhere('phone', 'like', '%' . $request->search . '%');
            })->orderBy('id', 'DESC')->paginate(10);

            $page_title = $this->page_title;

            return view($this->view_folder.'grid', compact('teams', 'page_title'));
        }
        else
        {
            return redirect()->route('home'); 
        }
    }
    // === End function ===

    // === Create new team page ===
    public function create()
    {
        if(Auth::user()->permissions)
        {
            if(Auth::user()->permissions->can_create)
            {
                $services = Service::all();
                $page_title = $this->page_title;
                $method = 'post';
                $submit_action = $this->submit_action; 
                return view($this->view_folder.'form', compact('page_title', 'method', 'submit_action', 'services'));
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

    // === Save new team ===
    public function store(TeamRequest $request)
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

                $team = $this->model::create([
                    'name' => $request->name,
                    'phone' => $request->phone,
                    'password' => bcrypt($request->password),
                    'email' => $request->email,
                    'image' => $image,
                ]);
                if(isset($request->service_id) && count($request->service_id) > 0){
                    foreach($request->service_id as $service){
                        $add = new TeamService();
                        $add->team_id = $team->id;
                        $add->service_id = $service;
                        $add->save();
                    }
                }
                return response()->json(['redirect' => $this->redirect_url, 'message' => $this->success_save], 200);
            }
        }     
    }
    // === End function ===

    // === Show team ===
    public function show($id)
    {
        if(Auth::user()->permissions)
        {
            if(Auth::user()->permissions->can_show)
            {
                $services = Service::all();
                $team = $this->model::find($id);
                $page_title = $this->page_title;
                $teamServices = TeamService::where('team_id',$team->id)->pluck('service_id')->toArray();
                return view($this->view_folder.'form', compact('teamServices', 'services', 'team', 'page_title'));
            }
        }   
    }
    // === End function ===

    // === Edit existing team ===
    public function edit($id)
    {
        if(Auth::user()->permissions)
        {
            if(Auth::user()->permissions->can_edit)
            {
                $services = Service::all();
                $team = $this->model::find($id);
                $method = 'put';
                $submit_action = route($this->prefix.'update', $id);
                $page_title = $this->page_title;
                $teamServices = TeamService::where('team_id',$team->id)->pluck('service_id')->toArray();
                return view($this->view_folder.'form', compact('team', 'teamServices', 'services','page_title', 'method', 'submit_action'));
            }
        }   
    }
    // === End function ===

    // === Confirm edit team ===
    public function update(TeamRequest $request, $id)
    {
        if(Auth::user()->permissions)
        {
            if(Auth::user()->permissions->can_edit)
            {
                $team['name'] = $request->name;
                $team['phone'] = $request->phone;
                $team['email'] = $request->email;
               
                if($request->filled('password'))
                {
                    $team['password'] = bcrypt($request->password);
                }
               
                if($request->has('image'))
                {
                    $team['image'] = uploadImage($request->image, $this->upload_folder);
                }

                $this->model::where('id', $id)->update($team);
                TeamService::where('team_id',$id)->delete();
                if(isset($request->service_id) && count($request->service_id) > 0){
                    foreach($request->service_id as $service){
                        $add = new TeamService();
                        $add->team_id = $id;
                        $add->service_id = $service;
                        $add->save();
                    }
                }
                
                return response()->json(['redirect' => $this->redirect_url, 'message' => $this->success_save], 200);
            }
        }     
    }
    // === End function ===

    // === Delete team ===
    public function destroy($id)
    {
        if(Auth::user()->permissions)
        {
            if(Auth::user()->permissions->can_delete)
            {
                $team = $this->model::find($id);
                $team->delete();
                return response()->json(['redirect' => $this->redirect_url, 'message' => trans('admin.success_delete')], 200);  
            }
        }
    }
    // === End function ===

    // === Active/Deactive team ===
    public function changeStatus(Request $request, $id, $status)
    {
        if($request->ajax())
        {
            $team = $this->model::find($id);
            
            if($team)
            {
                $team->active = $status;
                $team->save();
                $notification_data['image'] = 'logo.jpg';
                
                if($status == 0)
                {
                    $notification_data['message'] = trans('notification.admin_deactive_user');
                }
                else
                {
                    $notification_data['message'] = trans('notification.admin_active_user');
                }

                $notification_data['data'] = 201;
                sendNotification($notification_data, $team->player_id);
                return response()->json(['redirect' => $this->redirect_url, 'message' => $this->success_save], 200);
            }
        }
    }
    // === End function ===
    
}
