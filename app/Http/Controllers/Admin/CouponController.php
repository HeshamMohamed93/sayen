<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CouponRequest;
use App\Coupon;
use Auth;
use Carbon\Carbon;
use Illuminate\Routing\Route;
use App\Service;

class CouponController extends Controller
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
    
    function __construct(Request $request, Route $route, Coupon $coupon)
    {
        $this->prefix = 'coupons.';
        $this->model = $coupon;
        $this->upload_folder = 'coupons';
        $this->view_folder = 'admin.coupons.';
        $this->icon = 'fa fa-user-secret';
        
        $this->success_save = trans('admin.success_save');
        $this->redirect_url = route($this->prefix.'index'); 

        $current_method = $route->getActionMethod();

        if($current_method == 'index')
        {
            $this->page_title = trans('admin.coupons');
        }
        else if($current_method == 'create')
        {
            $this->page_title = trans('admin.add_new_coupon');
            $this->submit_action = route($this->prefix.'store'); 
        }
        else if($current_method == 'store')
        {
            $this->submit_action = route($this->prefix.'store'); 
        }
        else if($current_method == 'show')
        {
            $this->page_title = trans('admin.show_coupon');
        }
        else if($current_method == 'edit')
        {
            $this->page_title = trans('admin.edit_coupon');
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

    // === Get all coupons ===
    public function index(Request $request)
    {
        if(Auth::user()->permissions)
        {
            $coupons = (new Coupon)->newQuery();
            
            if($request->has('status'))
            {
                if($request->status != -1)
                {
                    $coupons->where('active', $request->status);
                }
                else
                {
                    return redirect()->route('coupons.index');
                }
            }

            $coupons = $coupons->when($request->search, function ($query) use ($request) {
                        $query->where('code', 'like', '%' . $request->search . '%')
                            ->orWhere('date_from', 'like', '%' . $request->search . '%')
                            ->orWhere('discount', 'like', '%' . $request->search . '%');
            })->orderBy('id', 'DESC')->paginate(10);

            $page_title = $this->page_title;

            return view($this->view_folder.'grid', compact('coupons', 'page_title'));
        }
        else
        {
            return redirect()->route('home'); 
        }
    }
    // === End function ===

    // === Create new coupon page ===
    public function create()
    {
        if(Auth::user()->permissions)
        {
            if(Auth::user()->permissions->can_create)
            {
                $page_title = $this->page_title;
                $services = Service::all();
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

    // === Save new coupon ===
    public function store(CouponRequest $request)
    {
        if(Auth::user()->permissions)
        {
            if(Auth::user()->permissions->can_create)
            {
                $this->model::create($request->all());
                return response()->json(['redirect' => $this->redirect_url, 'message' => $this->success_save], 200);
            }
        }     
    }
    // === End function ===

    // === Show coupon ===
    public function show($id)
    {
        if(Auth::user()->permissions)
        {
            if(Auth::user()->permissions->can_show)
            {
                $services = Service::withTrashed()->get();
                $coupon = $this->model::find($id);
                $coupon['expired_at'] = (isset($coupon['expired_at']))?Carbon::createFromFormat('Y-m-d', $coupon['expired_at']):Carbon::createFromFormat('Y-m-d',date('Y-m-d'));
                $coupon['date_from'] = (isset($coupon['date_from']))?Carbon::createFromFormat('Y-m-d', $coupon['date_from']):Carbon::createFromFormat('Y-m-d',date('Y-m-d'));
                $coupon['date_to'] = (isset($coupon['date_to']))?Carbon::createFromFormat('Y-m-d', $coupon['date_to']):Carbon::createFromFormat('Y-m-d',date('Y-m-d'));
                $page_title = $this->page_title;
                return view($this->view_folder.'form', compact('coupon', 'page_title', 'services'));
            }
        }   
    }
    // === End function ===

    // === Edit existing coupon ===
    public function edit($id)
    {
        if(Auth::user()->permissions)
        {
            if(Auth::user()->permissions->can_edit)
            {
                $services = Service::withTrashed()->get();
                $coupon = $this->model::find($id);
                $coupon['expired_at'] = (isset($coupon['expired_at']))?Carbon::createFromFormat('Y-m-d', $coupon['expired_at']):Carbon::createFromFormat('Y-m-d',date('Y-m-d'));
                $coupon['date_from'] = (isset($coupon['date_from']))?Carbon::createFromFormat('Y-m-d', $coupon['date_from']):Carbon::createFromFormat('Y-m-d',date('Y-m-d'));
                $coupon['date_to'] = (isset($coupon['date_to']))?Carbon::createFromFormat('Y-m-d', $coupon['date_to']):Carbon::createFromFormat('Y-m-d',date('Y-m-d'));
                $method = 'put';
                $submit_action = route($this->prefix.'update', $id);
                $page_title = $this->page_title;
                return view($this->view_folder.'form', compact('coupon', 'page_title', 'method', 'submit_action', 'services'));
            }
        }   
    }
    // === End function ===

    public function update(CouponRequest $request, $id)
    {
        if(Auth::user()->permissions)
        {
            if(Auth::user()->permissions->can_create)
            {
                $coupon = $request->except('_token', '_method');
                $this->model::where('id', $id)->update($coupon);
                return response()->json(['redirect' => $this->redirect_url, 'message' => $this->success_save], 200);
            }
        }
    }
    // === End function ===

    // === Delete coupon ===
    public function destroy($id)
    {
        if(Auth::user()->permissions)
        {
            if(Auth::user()->permissions->can_delete)
            {
                $coupon = $this->model::find($id);
                $coupon->delete();
                return response()->json(['redirect' => $this->redirect_url, 'message' => trans('admin.success_delete')], 200);  
            }
        }
    }
    // === End function ===

    // === Active/Deactive coupon ===
    public function changeStatus(Request $request, $id, $status)
    {
        if($request->ajax())
        {
            $coupon = $this->model::find($id);
            
            if($coupon)
            {
                $coupon->active = $status;
                $coupon->save();
                return response()->json(['redirect' => $this->redirect_url, 'message' => $this->success_save], 200);
            }
        }
    }
    // === End function ===
}
