<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Routing\Route;
use App\User;
use Auth;
use App\Http\Requests\Admin\UserRequest;
use App\Building;
use App\Order;

class UserController extends Controller
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

    function __construct(Request $request, Route $route, User $user)
    {
        $this->prefix = 'users.';
        $this->model = $user;
        $this->upload_folder = 'users';
        $this->view_folder = 'admin.users.';
        $this->icon = 'fa fa-users';
        
        $this->success_save = trans('admin.success_save');
        $this->redirect_url = route($this->prefix.'index'); 

        $current_method = $route->getActionMethod();

        if($current_method == 'index')
        {
            $this->page_title = trans('admin.users');
        }
        else if($current_method == 'create')
        {
            $this->page_title = trans('admin.add_new_user');
            $this->submit_action = route($this->prefix.'store'); 
        }
        else if($current_method == 'store')
        {
            $this->submit_action = route($this->prefix.'store'); 
        }
        else if($current_method == 'show')
        {
            $this->page_title = trans('admin.show_user');
        }
        else if($current_method == 'edit')
        {
            $this->page_title = trans('admin.edit_user');
        }
        else if($current_method == 'update')
        {
            $this->page_title = trans('admin.edit_user');
        }
        else if($current_method == 'destroy')
        {
            $this->page_title = trans('admin.success_delete');
        }
    }

    // === Get all users ===
    public function index(Request $request)
    {
        if(Auth::user()->permissions)
        {
            $users = (new User)->newQuery();
            
            if($request->has('status') && $request->status != 2)
            {
                if($request->status != -1)
                {
                    $users->where('active', $request->status);
                }
            }
            
            if($request->has('status') && $request->status == 2){
                $users = $users->onlyTrashed();
            }
            
            if($request->has('building'))
            {
                ($request->building != 0)?$users->where('building_id', $request->building):'';
            }
            
            if($request->search){
                $users = $users->where(function($query) use ($request){
                    $query->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('email', 'like', '%' . $request->search . '%')
                        ->orWhere('phone', 'like', '%' . $request->search . '%');
                });
            }
            $users = $users->orderBy('id', 'DESC')->paginate(50);
            
            $page_title = $this->page_title;
            $buildings = Building::orderBy('name','asc')->pluck('name','id')->toArray();
            return view($this->view_folder.'grid', compact('users', 'page_title','buildings'));
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
                $buildings = Building::withTrashed()->get();
                $page_title = $this->page_title;
                $method = 'post';
                $submit_action = $this->submit_action; 
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

    // === Save new team ===
    public function store(UserRequest $request)
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
                $this->model::create([
                    'name' => $request->name,
                    'last_name' => $request->name,
                    'phone' => $request->phone,
                    'excellence_client' => $request->excellence_client,
                    'building_id' => $request->building_id,
                    'password' => bcrypt($request->password),
                    'email' => $request->email,
                    'image' => $image,
                ]);
                
                return response()->json(['redirect' => $this->redirect_url, 'message' => $this->success_save], 200);
            }
        }     
    }
    // === End function ===

    // === Edit existing user ===
    public function edit($id)
    {
        if(Auth::user()->permissions)
        {
            if(Auth::user()->permissions->can_edit)
            {
                $user = $this->model::withTrashed()->find($id);
                $buildings = Building::withTrashed()->get();
                $method = 'put';
                $submit_action = route($this->prefix.'update', $id);
                $page_title = $this->page_title;
                $orders = Order::where('user_id',$id)->get();
                return view($this->view_folder.'form', compact('orders','user', 'page_title', 'method', 'submit_action', 'buildings'));
            }
        }   
    }
    // === End function ===

    // === Confirm edit user ===
    public function update(UserRequest $request, $id)
    {
        if(Auth::user()->permissions)
        {
            if(Auth::user()->permissions->can_edit)
            {
                $user['name'] = $request->name;
                $user['last_name'] = $request->last_name;
                $user['phone'] = $request->phone;
                $user['email'] = $request->email;
                $user['excellence_client'] = $request->excellence_client;

                if($request->excellence_client == 1)
                {
                    $user['building_id'] = $request->building_id;
                    $user['flat'] = $request->flat;
                }
                else
                {
                    $user['building_id'] = null;
                    $user['flat'] = null;
                }
                $user['excellence_client_verified'] = $request->excellence_client_verified; 
                if($request->filled('password'))
                {
                    $user['password'] = bcrypt($request->password);
                }

                if($request->has('image'))
                {
                    $user['image'] = uploadImage($request->image, $this->upload_folder);
                }
                $user['deleted_at'] = null;
                $this->model::withTrashed()->where('id', $id)->update($user);
                
                return response()->json(['redirect' => $this->redirect_url, 'message' => $this->success_save], 200);
            }
        }     
    }
    // === End function ===

    // === Delete user ===
    public function destroy($id)
    {
        if(Auth::user()->permissions)
        {
            if(Auth::user()->permissions->can_delete)
            {
                $user = $this->model::find($id);
                $user->delete();
                return response()->json(['redirect' => $this->redirect_url, 'message' => trans('admin.success_delete')], 200);  
            }
        }
    }
    // === End function ===

    // === Active/Deactive user ===
    public function changeStatus(Request $request, $id, $status)
    {
        if($request->ajax())
        {
            $user = $this->model::find($id);
            if($user)
            {
                $user->active = $status;
                $user->save();
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
                sendNotification($notification_data, $user->player_id);
                return response()->json(['redirect' => $this->redirect_url, 'message' => $this->success_save], 200);
            }
        }
    }
    // === End function ===
    
}
