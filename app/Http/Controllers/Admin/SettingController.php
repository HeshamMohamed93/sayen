<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Setting;
use Auth;
use Illuminate\Routing\Route;
use App\Http\Requests\Admin\SettingRequest;
use DB;

class SettingController extends Controller
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

    function __construct(Request $request, Route $route, Setting $setting)
    {
        $this->prefix = 'settings.';
        $this->model = $setting;
        $this->upload_folder = 'settings';
        $this->view_folder = 'admin.settings.';
        $this->icon = 'fa fa-user-cog';
        
        $this->success_save = trans('admin.success_save');
        $this->redirect_url = route($this->prefix.'index'); 

        $current_method = $route->getActionMethod();

        if($current_method == 'index')
        {
            $this->page_title = trans('admin.settings');
        }
        else if($current_method == 'update')
        {
            $this->page_title = trans('admin.edit_admin');
        }
    }

    // === Settings ===
    public function index()
    {
        if(Auth::user()->permissions)
        {
            if(Auth::user()->permissions->can_edit || Auth::user()->permissions->can_show)
            {
                $setting = Setting::find(1);
                $page_title = $this->page_title;
                $method = null;
                $submit_action = null;

                if(Auth::user()->permissions->can_edit)
                {
                    $method = 'put';
                    $submit_action = route($this->prefix.'update', 1);
                }
                $images = DB::table('images_maintenanance_report')->get();
                return view($this->view_folder.'form', compact('setting', 'page_title', 'method', 'submit_action','images'));
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

    //=== Update settings ===
    public function update(SettingRequest $request, $id)
    {
        if(Auth::user()->permissions)
        {
            if(Auth::user()->permissions->can_edit)
            {
                $setting_data = $request->except('_method', '_token','images');
                $this->model::where('id', 1)->update($setting_data);
                if($request->has('images'))
                {   
                    $images = $request->images;
                    foreach($images as $img){
                        $image = uploadImage($img,'');
                        DB::table('images_maintenanance_report')->insert(['image'=>$image]);
                    }
                }
                return response()->json(['redirect' => $this->redirect_url, 'message' => $this->success_save], 200);
            }
        }
    }
    //=== End function ===

}
