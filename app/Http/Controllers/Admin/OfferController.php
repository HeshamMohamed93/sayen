<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OfferRequest;
use App\Offer;
use App\Service;
use Auth;
use Illuminate\Routing\Route;
use DB;

class OfferController extends Controller
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

    function __construct(Request $request, Route $route, Offer $offer)
    {
        $this->prefix = 'offers.';
        $this->model = $offer;
        $this->upload_folder = 'offers';
        $this->view_folder = 'admin.offers.';
        $this->icon = 'fa fa-gift';
        
        $this->success_save = trans('admin.success_save');
        $this->redirect_url = route($this->prefix.'index'); 

        $current_method = $route->getActionMethod();

        if($current_method == 'index')
        {
            $this->page_title = trans('admin.offers');
        }
        else if($current_method == 'create')
        {
            $this->page_title = trans('admin.add_new_offer');
            $this->submit_action = route($this->prefix.'store'); 
        }
        else if($current_method == 'store')
        {
            $this->submit_action = route($this->prefix.'store'); 
        }
        else if($current_method == 'show')
        {
            $this->page_title = trans('admin.show_offer');
        }
        else if($current_method == 'edit')
        {
            $this->page_title = trans('admin.edit_offer');
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
            $offers = $this->model::when($request->search, function ($query) use ($request) {
                        $query->where('title', 'like', '%' . $request->search . '%')
                            ->orWhere('price', 'like', '%' . $request->search . '%');
            })->orderBy('id', 'DESC')->paginate(10);
            $page_title = $this->page_title;
            return view($this->view_folder.'grid', compact('offers', 'page_title'));
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
                $services = Service::where('active',1)->where('parent_id',0)->pluck('name','id')->toArray();
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
    public function store(OfferRequest $request)
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
                    'price' => $request->price,
                    'service_id' => $request->service_id,
                    'image' => $image,
                    'status' => $request->active,
                    'from' => $request->from,
                    'to' => $request->to,
                    'show' => $request->show,
                    'date' => date('Y-m-d'),
                    'text' => $request->text
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
                $offer = $this->model::find($id);
                $page_title = $this->page_title;
                $services = Service::pluck('name','id')->toArray();
                return view($this->view_folder.'form', compact('offer', 'page_title','services'));
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
                $offer = $this->model::find($id);
                $method = 'put';
                $submit_action = route($this->prefix.'update', $id);
                $page_title = $this->page_title;
                $services = Service::pluck('name','id')->toArray();
                return view($this->view_folder.'form', compact('offer', 'page_title', 'method', 'submit_action','services'));
            }
        }   
    }
    // === End function ===

    // === Confirm edit service ===
    public function update(OfferRequest $request, $id)
    {
        if(Auth::user()->permissions)
        {
            if(Auth::user()->permissions->can_edit)
            {
                $offer['title'] = $request->title;
                $offer['title_en'] = $request->title_en;
                $offer['price'] = $request->price;
                $offer['status'] = $request->active;
                $offer['service_id'] = $request->service_id;
                $offer['text'] = $request->text;
                $offer['from'] = $request->from;
                $offer['to'] = $request->to;
                $offer['show'] = $request->show;
                if($request->has('image'))
                {
                    $offer['image'] = uploadImage($request->image, $this->upload_folder);
                }

                $this->model::where('id', $id)->update($offer);
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
                $offer = $this->model::find($id);
                $offer->delete();
                return response()->json(['redirect' => $this->redirect_url, 'message' => trans('admin.success_delete')], 200);  
            }
        }
    }
}