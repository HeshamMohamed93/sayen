<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StaticPageRequest;
use App\StaticPage;
use Auth;
use Illuminate\Routing\Route;

class StaticPageController extends Controller
{
    private $model;
    private $view_folder;
    private $submit_action;
    private $page_title;
    private $redirect_url;
    private $success_save;
    private $success_delete;
    private $prefix;

    function __construct(Request $request, Route $route, StaticPage $static_page)
    {
        $this->prefix = 'static-pages.';
        $this->model = $static_page;
        $this->view_folder = 'admin.static_pages.';
        $this->icon = 'fa fa-user-secret';
        
        $this->success_save = trans('admin.success_save');
        $this->redirect_url = route($this->prefix.'index'); 

        $current_method = $route->getActionMethod();

        if($current_method == 'index')
        {
            $this->page_title = trans('admin.static_pages');
        }
        else if($current_method == 'show')
        {
            $this->page_title = trans('admin.show_static_pages');
        }
        else if($current_method == 'edit')
        {
            $this->page_title = trans('admin.edit_static_pages');
        }
        else if($current_method == 'update')
        {
            $this->page_title = trans('admin.edit_static_pages');
        }
    }

   // === Get all pages ===
   public function index(Request $request)
   {
       if(Auth::user()->permissions)
       {
           $pages = $this->model::when($request->search, function ($query) use ($request) {
                       $query->where('title', 'like', '%' . $request->search . '%')
                           ->orWhere('content', 'like', '%' . $request->search . '%');
           })->orderBy('id', 'DESC')->paginate(10);

           $page_title = $this->page_title;

           return view($this->view_folder.'grid', compact('pages', 'page_title'));
       }
       else
       {
           return redirect()->route('home'); 
       }
   }
   // === End function ===

    // === Show page ===
    public function show($id)
    {
        if(Auth::user()->permissions)
        {
            if(Auth::user()->permissions->can_show)
            {
                $page = $this->model::find($id);
                $page_title = $this->page_title;
                return view($this->view_folder.'form', compact('page', 'page_title'));
            }
        }   
    }
    // === End function ===

    // === Edit existing page ===
    public function edit($id)
    {
        if(Auth::user()->permissions)
        {
            if(Auth::user()->permissions->can_edit)
            {
                $page = $this->model::find($id);
                $method = 'put';
                $submit_action = route($this->prefix.'update', $id);
                $page_title = $this->page_title;

                return view($this->view_folder.'form', compact('page', 'page_title', 'method', 'submit_action'));
            }
        }   
    }
    // === End function ===

    // === Confirm edit page ===
    public function update(StaticPageRequest $request, $id)
    {
        if(Auth::user()->permissions)
        {
            if(Auth::user()->permissions->can_edit)
            {
                $page = $request->except(['_token', '_method']);

                $this->model::where('id', $id)->update($page);
                
                return response()->json(['redirect' => $this->redirect_url, 'message' => $this->success_save], 200);
            }
        }     
    }
    // === End function ===
}
