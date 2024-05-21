<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\ContactUs;
use Illuminate\Routing\Route;
use Auth;

class ContactUsController extends Controller
{
    function __construct(Request $request, Route $route, ContactUs $contact_us)
    {
        $this->prefix = 'contact-us.';
        $this->model = $contact_us;
        $this->upload_folder = 'contact_us';
        $this->view_folder = 'admin.contact_us.';
        $this->icon = 'fa fa-user-secret';
        
        $this->redirect_url = route($this->prefix.'index'); 

        $current_method = $route->getActionMethod();

        if($current_method == 'index')
        {
            $this->page_title = trans('admin.contact_us');
        }
        else if($current_method == 'show')
        {
            $this->page_title = trans('admin.show_message');
        }
    }

    // === Get all messages ===
    public function index(Request $request)
    {
        if(Auth::user()->permissions)
        {
            $messages = $this->model::when($request->search, function ($query) use ($request) {
                        $query->where('name', 'like', '%' . $request->search . '%')
                            ->orWhere('message', 'like', '%' . $request->search . '%');
            })->orderBy('id', 'DESC')->paginate(10);

            $page_title = $this->page_title;
            
            return view($this->view_folder.'grid', compact('messages', 'page_title'));
        }
        else
        {
            return redirect()->route('home'); 
        }
    }
    // === End function ===

    // === Show message ===
    public function show($id)
    {
        if(Auth::user()->permissions)
        {
            if(Auth::user()->permissions->can_show)
            {
                $message = $this->model::find($id);
                $page_title = $this->page_title;
                return view($this->view_folder.'form', compact('message', 'page_title'));
            }
        }   
    }
    // === End function ===
}
