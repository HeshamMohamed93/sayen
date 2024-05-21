<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CommonQuestionRequest;
use App\CommonQuestion;
use Auth;
use Illuminate\Routing\Route;

class CommonQuestionController extends Controller
{
    private $model;
    private $view_folder;
    private $submit_action;
    private $page_title;
    private $redirect_url;
    private $success_save;
    private $success_delete;
    private $prefix;

    function __construct(Request $request, Route $route, CommonQuestion $common_questions)
    {
        $this->prefix = 'common-questions.';
        $this->model = $common_questions;
        $this->view_folder = 'admin.common_questions.';
        $this->icon = 'fa fa-user-secret';
        
        $this->success_save = trans('admin.success_save');
        $this->redirect_url = route($this->prefix.'index'); 

        $current_method = $route->getActionMethod();

        if($current_method == 'index')
        {
            $this->page_title = trans('admin.common_questions');
        }
        else if($current_method == 'create')
        {
            $this->page_title = trans('admin.add_new_question');
            $this->submit_action = route($this->prefix.'store'); 
        }
        else if($current_method == 'store')
        {
            $this->submit_action = route($this->prefix.'store'); 
        }
        else if($current_method == 'show')
        {
            $this->page_title = trans('admin.show_question');
        }
        else if($current_method == 'edit')
        {
            $this->page_title = trans('admin.edit_question');
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

   // === Get all questions ===
   public function index(Request $request)
   {
       if(Auth::user()->permissions)
       {
           $questions = $this->model::when($request->search, function ($query) use ($request) {
                       $query->where('question', 'like', '%' . $request->search . '%')
                           ->orWhere('answer', 'like', '%' . $request->search . '%');
           })->orderBy('id', 'DESC')->paginate(10);

           $page_title = $this->page_title;

           return view($this->view_folder.'grid', compact('questions', 'page_title'));
       }
       else
       {
           return redirect()->route('home'); 
       }
   }
   // === End function ===

    // === Create new question page ===
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

    // === Save new question ===
    public function store(CommonQuestionRequest $request)
    {
        if(Auth::user()->permissions)
        {
            if(Auth::user()->permissions->can_create)
            {
                $this->model::create([
                    'question' => $request->question,
                    'answer' => $request->answer,
                    'lang' => $request->lang,
                ]);
                
                return response()->json(['redirect' => $this->redirect_url, 'message' => $this->success_save], 200);
            }
        }     
    }
    // === End function ===

    // === Show question ===
    public function show($id)
    {
        if(Auth::user()->permissions)
        {
            if(Auth::user()->permissions->can_show)
            {
                $question = $this->model::find($id);
                $page_title = $this->page_title;
                return view($this->view_folder.'form', compact('question', 'page_title'));
            }
        }   
    }
    // === End function ===

    // === Edit existing question ===
    public function edit($id)
    {
        if(Auth::user()->permissions)
        {
            if(Auth::user()->permissions->can_edit)
            {
                $question = $this->model::find($id);
                $method = 'put';
                $submit_action = route($this->prefix.'update', $id);
                $page_title = $this->page_title;

                return view($this->view_folder.'form', compact('question', 'page_title', 'method', 'submit_action'));
            }
        }   
    }
    // === End function ===

    // === Confirm edit question ===
    public function update(CommonQuestionRequest $request, $id)
    {
        if(Auth::user()->permissions)
        {
            if(Auth::user()->permissions->can_edit)
            {
                $question = $request->except(['_token', '_method']);

                $this->model::where('id', $id)->update($question);
                
                return response()->json(['redirect' => $this->redirect_url, 'message' => $this->success_save], 200);
            }
        }     
    }
    // === End function ===

    // === Delete question ===
    public function destroy($id)
    {
        if(Auth::user()->permissions)
        {
            if(Auth::user()->permissions->can_delete)
            {
                $question = $this->model::find($id);
                $question->delete();
                return response()->json(['redirect' => $this->redirect_url, 'message' => trans('admin.success_delete')], 200);  
            }
        }
    }
    // === End function ===
}
