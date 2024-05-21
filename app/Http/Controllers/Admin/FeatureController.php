<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Feature;
use App\Http\Requests\Admin\FeatureRequest;
use Auth;
use Illuminate\Routing\Route;

class FeatureController extends Controller
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

    function __construct(Request $request, Route $route, Feature $feature)
    {
        $this->prefix = 'features.';
        $this->model = $feature;
        $this->upload_folder = 'features';
        $this->view_folder = 'admin.features.';
        $this->icon = 'fa fa-user-secret';
        
        $this->success_save = trans('admin.success_save');
        $this->redirect_url = route($this->prefix.'index'); 

        $current_method = $route->getActionMethod();

        if($current_method == 'index')
        {
            $this->page_title = trans('admin.features');
        }
        else if($current_method == 'create')
        {
            $this->page_title = trans('admin.add_new_feature');
            $this->submit_action = route($this->prefix.'store'); 
        }
        else if($current_method == 'store')
        {
            $this->submit_action = route($this->prefix.'store'); 
        }
        else if($current_method == 'show')
        {
            $this->page_title = trans('admin.show_feature');
        }
        else if($current_method == 'edit')
        {
            $this->page_title = trans('admin.edit_feature');
        }
        else if($current_method == 'destroy')
        {
            $this->page_title = trans('admin.success_delete');
        }
    }

    // === Get all features ===
    public function index(Request $request)
    {
        if(Auth::user()->permissions)
        {
            if(Auth::user()->permissions->can_create || Auth::user()->permissions->can_edit 
            || Auth::user()->permissions->can_show || Auth::user()->permissions->can_delete)
            {

                $features = $this->model::when($request->search, function ($query) use ($request) {
                            $query->where('title', 'like', '%' . $request->search . '%')
                                ->orWhere('content', 'like', '%' . $request->search . '%');
                })->orderBy('id', 'DESC')->paginate(10);

                $page_title = $this->page_title;

                return view($this->view_folder.'grid', compact('features', 'page_title'));
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

    // === Create new feature page ===
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

    // === Save new feature ===
    public function store(FeatureRequest $request)
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
                    'image' => $image,
                    'title' => $request->title,
                    'content' => $request->content
                ]);
                
                return response()->json(['redirect' => $this->redirect_url, 'message' => $this->success_save], 200);
            }
        }     
    }
    // === End function ===

    // === Show feature ===
    public function show($id)
    {
        if(Auth::user()->permissions)
        {
            if(Auth::user()->permissions->can_show)
            {
                $feature = $this->model::find($id);
                $page_title = $this->page_title;
                return view($this->view_folder.'form', compact('feature', 'page_title'));
            }
            else
            {
                return redirect()->route('home'); 
            }
        }   
    }
    // === End function ===

    // === Edit existing feature ===
    public function edit($id)
    {
        if(Auth::user()->permissions)
        {
            if(Auth::user()->permissions->can_edit)
            {
                $feature = $this->model::find($id);
                $method = 'put';
                $submit_action = route($this->prefix.'update', $id);
                $page_title = $this->page_title;

                return view($this->view_folder.'form', compact('feature', 'page_title', 'method', 'submit_action'));
            }
        }   
    }
    // === End function ===

    // === Confirm edit feature ===
    public function update(FeatureRequest $request, $id)
    {
        if(Auth::user()->permissions)
        {
            if(Auth::user()->permissions->can_edit)
            {
                $feature['title'] = $request->title;
                $feature['content'] = $request->content;

                if($request->has('image'))
                {
                    $feature['image'] = uploadImage($request->image, $this->upload_folder);
                }

                $this->model::where('id', $id)->update($feature);
                
                return response()->json(['redirect' => $this->redirect_url, 'message' => $this->success_save], 200);
            }
        }     
    }
    // === End function ===

    // === Delete feature ===
    public function destroy($id)
    {
        if(Auth::user()->permissions)
        {
            if(Auth::user()->permissions->can_delete)
            {
                $feature = $this->model::find($id);
                $feature->delete();
                return response()->json(['redirect' => $this->redirect_url, 'message' => trans('admin.success_delete')], 200);  
            }
        }
    }
    // === End function ===


}
