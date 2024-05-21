<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminRequest;
use Auth;
use App\Admin;
use App\Module;
use App\AdminPermission;
use Illuminate\Routing\Route;
use App\Service;
use App\AdminService;
use App\Offer;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use App\User;
use FCM;
use App\Order;
use App\EmergencyOrder;
use DB;
use File;

class AdminController extends Controller
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

    function __construct(Request $request, Route $route, Admin $admin)
    {
        $this->prefix = 'admins.';
        $this->model = $admin;
        $this->upload_folder = 'admins';
        $this->view_folder = 'admin.admins.';
        $this->icon = 'fa fa-user-secret';

        $this->success_save = trans('admin.success_save');
        $this->redirect_url = route($this->prefix.'index');

        $current_method = $route->getActionMethod();

        if($current_method == 'index')
        {
            $this->page_title = trans('admin.admins');
        }
        else if($current_method == 'create')
        {
            $this->page_title = trans('admin.add_new_admin');
            $this->submit_action = route($this->prefix.'store');
        }
        else if($current_method == 'store')
        {
            $this->submit_action = route($this->prefix.'store');
        }
        else if($current_method == 'show')
        {
            $this->page_title = trans('admin.show_admin');
        }
        else if($current_method == 'edit')
        {
            $this->page_title = trans('admin.edit_admin');
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

    // === Get all admins ===
    public function index(Request $request)
    {
        if(Auth::user()->permissions)
        {
            $admins = $this->model::when($request->search, function ($query) use ($request) {
                        $query->where('name', 'like', '%' . $request->search . '%')
                            ->orWhere('email', 'like', '%' . $request->search . '%');
            })->where('id', '<>', Auth::user()->id)->orderBy('id', 'DESC')->paginate(10);

            $page_title = $this->page_title;
            //activity()->log('Look mum, I logged something');
            return view($this->view_folder.'grid', compact('admins', 'page_title'));
        }
        else
        {
            return redirect()->route('home');
        }
    }
    // === End function ===

    // === Create new admin page ===
    public function create()
    {
        if(Auth::user()->permissions)
        {
            if(Auth::user()->permissions->can_create)
            {
                $moduless = Module::all();
                $page_title = $this->page_title;
                $method = 'post';
                $submit_action = $this->submit_action;
                $services = Service::all();
                return view($this->view_folder.'form', compact('moduless', 'page_title', 'method', 'submit_action','services'));
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
    
    // === Save new admin ===
    public function store(AdminRequest $request)
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

                $save_admin = $this->model::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => bcrypt($request->password),
                    'image' => $image,
                    'type' => $request->type
                ]);
                if(isset($request->service_id) && count($request->service_id) > 0 && $request->type == 'service'){
                    foreach($request->service_id as $service){
                        $add = new AdminService();
                        $add->admin_id = $save_admin->id;
                        $add->service_id = $service;
                        $add->save();
                    }
                }
                if($save_admin)
                {
                    $this->setPermissions($save_admin->id, $request);
                }

                return response()->json(['redirect' => $this->redirect_url, 'message' => $this->success_save], 200);
            }
        }
    }
    // === End function ===

    // === Show admin ===
    public function show($id)
    {
        if(Auth::user()->permissions)
        {
            if(Auth::user()->permissions->can_show)
            {
                $admin = $this->model::find($id);
                $admin_permissions = AdminPermission::where('admin_id', $id)->get();
                $moduless = Module::all();
                $page_title = $this->page_title;
                $services = Service::all();
                $adminServices = AdminService::where('admin_id',$admin->id)->pluck('service_id')->toArray();
                return view($this->view_folder.'form', compact('admin', 'moduless', 'page_title', 'admin_permissions','adminServices','services'));
            }
        }
    }
    // === End function ===

    // === Edit existing admin ===
    public function edit($id)
    {
        if(Auth::user()->permissions)
        {
            if(Auth::user()->permissions->can_edit)
            {
                $admin = $this->model::find($id);
                $admin_permissions = AdminPermission::where('admin_id', $id)->get();
                $moduless = Module::all();
                $method = 'put';
                $submit_action = route($this->prefix.'update', $id);
                $page_title = $this->page_title;
                $services = Service::all();
                $adminServices = AdminService::where('admin_id',$admin->id)->pluck('service_id')->toArray();
                return view($this->view_folder.'form', compact('admin', 'moduless', 'page_title', 'admin_permissions', 'method', 'submit_action','adminServices','services'));
            }
        }
    }
    // === End function ===

    // === Confirm edit admin ===
    public function update(AdminRequest $request, $id)
    {
        if(Auth::user()->permissions)
        {
            if(Auth::user()->permissions->can_edit)
            {
                $admin['name'] = $request->name;
                $admin['email'] = $request->email;
                $admin['type'] = $request->type;

                if($request->has('image'))
                {
                    $admin['image'] = uploadImage($request->image, $this->upload_folder);
                }

                if($request->filled('password'))
                {
                    $admin['password'] = bcrypt($request->password);
                }
                $admin['show_order_deleted'] = $request->show_order_deleted;
                $admin['show_client_deleted'] = $request->show_client_deleted;
                $save_admin = $this->model::where('id', $id)->firstOrFail()->update($admin);
                AdminService::where('admin_id',$id)->delete();
                if(isset($request->service_id) && count($request->service_id) > 0 && $request->type == 'service'){
                    foreach($request->service_id as $service){
                        $add = new AdminService();
                        $add->admin_id = $id;
                        $add->service_id = $service;
                        $add->save();
                    }
                }
                if($save_admin)
                {
                    $this->setPermissions($id, $request);
                }

                return response()->json(['redirect' => $this->redirect_url, 'message' => $this->success_save], 200);
            }
        }
    }
    // === End function ===

    // === Open user profile ===
    public function profile()
    {
        $moduless = Module::all();
        $page_title = trans('admin.profile');
        $method = 'put';
        $submit_action =route('update-profile');
        $admin = Auth::user();
        return view($this->view_folder.'profile', compact('admin', 'moduless', 'page_title', 'method', 'submit_action'));
    }
    // === End function ===

    // === Confirm update profile ===
    public function updateProfile(AdminRequest $request)
    {
        $admin['name'] = $request->name;
        $admin['email'] = $request->email;
        $admin['password'] = bcrypt($request->password);

        if($request->has('image'))
        {
            $admin['image'] = uploadImage($request->image, $this->upload_folder);
        }

        $save_admin = $this->model::where('id', Auth::user()->id)->update($admin);

        return response()->json(['redirect' => route('home'), 'message' => $this->success_save], 200);
    }
    // === End function ===

    // === Delete admin ===
    public function destroy($id)
    {
        if(Auth::user()->permissions)
        {
            if(Auth::user()->permissions->can_delete)
            {
                $admin = $this->model::find($id);
                $admin->delete();
                return response()->json(['redirect' => $this->redirect_url, 'message' => trans('admin.success_delete')], 200);
            }
        }
    }
    // === End function ===

    // === Set permission to admin ===
    private function setPermissions($admin_id, $request)
    {
        AdminPermission::where('admin_id', $admin_id)->delete();
        $permissions = ['can_create', 'can_edit', 'can_show', 'can_delete'];

        foreach($permissions as $permission)
        {
            if($request->has($permission))
            {
                foreach($request[$permission] as $module)
                {
                    $match_module = ['admin_id' => $admin_id, 'module_id' => $module];
                    AdminPermission::updateOrCreate($match_module, ['admin_id' => $admin_id, 'module_id' => $module, $permission => '1']);
                }
            }
        }
    }
    // === End function ===
    public function printMaintenanance(Request $request){
        // Fetch all customers from database
        $path = base_path().'/public/uploads/';
        if($request->type == 'order'){
            $data = Order::where('id', $request->id)->first();
        }else{
            $data = EmergencyOrder::where('id', $request->id)->first();
        }
        $workDetails = $request->workDetails;
        $handWork = $request->handWork;
        $materialsUsed = $request->materialsUsed;
        $handWorkPrice = $request->handWorkPrice;
        $materialsUsedPrice = $request->materialsUsedPrice;

        $report = DB::table('maintenanance_report')->where('type',$request->type)->where('order_id',$request->id)->first();
        if($report){
            $check = DB::table('images_maintenanance_report')->where('id',$report->image)->first();
            if($check){
                $image = $check->image;
            }else{
                $image = null;
            }
        }else{
            $image = null;
        }
        set_time_limit(300);
        ini_set('max_execution_time', '300');
        ini_set("pcre.backtrack_limit", "5000000");
        $html = view('admin.print_maintenanance_pdf',compact('data','workDetails','handWork','materialsUsed','image','handWorkPrice','materialsUsedPrice'))->render(); // file render
        $mpdf = new \Mpdf\Mpdf(['tempDir' => storage_path('/tmp')]);
        $mpdf->WriteHTML($html);
        $mpdf->Output($path.'print_maintenanance_'.$data->order_number.'.pdf','F');
        $file_path = 'uploads/print_maintenanance_'.$data->order_number.'.pdf';
        //return response()->download(public_path($file_path));
        //return \Storage::disk('public')->download($file_path);
        //return  response()->download($file_path, 'print_maintenanance_'.$data->order_number.'.pdf');
        //return response()->download('https://sayen.co/public/uploads/print_maintenanance_'.$data->order_number.'.pdf'); 
        return response()->json(['path'=>'https://sayen.co/public/uploads/print_maintenanance_'.$data->order_number.'.pdf']);
    }
    public function printOnePageMaintenanance(Request $request){
        if($request->type == 'order'){
            $data = Order::where('id', $request->id)->first();
        }else{
            $data = EmergencyOrder::where('id', $request->id)->first();
        }
        $workDetails = $request->workDetails;
        $handWork = $request->handWork;
        $materialsUsed = $request->materialsUsed;
        $handWorkPrice = $request->handWorkPrice;
        $materialsUsedPrice = $request->materialsUsedPrice;

        $report = DB::table('maintenanance_report')->where('type',$request->type)->where('order_id',$request->id)->first();
        if($report){
            $check = DB::table('images_maintenanance_report')->where('id',$report->image)->first();
            if($check){
                $image = $check->image;
            }else{
                $image = null;
            }
        }else{
            $image = null;
        }
        set_time_limit(300);
        ini_set('max_execution_time', '300');
        ini_set("pcre.backtrack_limit", "5000000");
        $html = view('admin.print_maintenanance_pdf',compact('data','workDetails','handWork','materialsUsed','image','handWorkPrice','materialsUsedPrice'))->render(); // file render
        return response()->json(['data' => $html, 'message' => trans('admin.success_add')], 200);
    }
    public function saveEditMaintenananceReport(Request $request){
        // $image = null;
        // if($request->has('image'))
        // {
        //     $image = uploadImage($request->image,'');
        // }
        $check = DB::table('maintenanance_report')->where('type',$request->type)->where('order_id',$request->id)->first();
        if($check){
            // if($check->image){
            //     deleteImage($check->image,'');
            // }
            DB::table('maintenanance_report')->where('id',$check->id)->update(['work_details'=>$request->work_details,'hand_work'=>$request->hand_work,'hand_work_price'=>$request->hand_work_price,'materials_used'=>$request->materials_used,'materials_used_price'=>$request->materials_used_price,'type'=>$request->type,'image'=> $request->image_id]);
        }else{
            DB::table('maintenanance_report')->insert(['order_id'=>$request->id,'work_details'=>$request->work_details,'hand_work'=>$request->hand_work,'hand_work_price'=>$request->hand_work_price,'materials_used'=>$request->materials_used,'materials_used_price'=>$request->materials_used_price,'type'=>$request->type,'image'=>$request->image_id]);
        }
        if($request->type == 'order'){
            $url = $request->id;
        }else{
            $url = $request->id;
        }
        return response()->json(['redirect' => $url, 'message' => trans('admin.success_add')], 200);
    }
    public function deleteOneImage($id){
        $image = DB::table('images_maintenanance_report')->where('id',$id)->first();
        //$path =  url('public/uploads/').'/'.$image->image;
        deleteImage($image->image,'');
        DB::table('images_maintenanance_report')->where('id',$id)->delete();
        $url = url('admin-panel/settings');
        return response()->json(['redirect' => $url, 'message' => trans('admin.success_delete')], 200);
    }
    // === Set permission to admin ===
    public function changeService(Request $request)
    {
        $services = [];
        if($request->id != 0){
            $services = Service::where('parent_id',$request->id)->where('active',1)->pluck('name','id')->toArray();
        }
        $html = view('admin.offers.changeServices',compact('services'))->render();
        return $html;
    }
    // === End function ===

    public function getServices()
    {
        $services = Service::where('active', '1')->where('parent_id',0)->select('id','name','initial_price','active','warranty','deleted_at','image','text')->get();
        
        foreach($services as $service){
            if($service['text'] == null){
                $service['text'] = '';
            }
            $checkOffer = Offer::where('service_id',$service->id)->where('status',1)->first();
            $supservicesIDS = Service::where('active', '1')->where('parent_id',$service->id)->pluck('id')->toArray();
            $checkSubOffer =  Offer::whereIN('service_id',$supservicesIDS)->where('status',1)->first();
            if($checkOffer || $checkSubOffer){
                $service['offer'] = 1;
            }else{
                $service['offer'] = 0;
            }
            $service['checkSub'] = $service->checkSub();
        }
        
        $offers = Offer::where('status',1)->where('show',1)->with('service')->select('id','title','price','service_id','from','to','image')->get();
        foreach($offers as $key => $offer){
            unset($offers[$key]['service']['name']);
            unset($offers[$key]['service']['name_en']);

            
            $offers[$key]['service']['name'] = $offer->Service->name;
            
            unset($offer->Service);
            $offer['service']['offer'] = 1;
        }
        return response()->json(['services' => $services,'offers' => $offers]);    
    }

    // === Set permission to admin ===
    public function offerSendNotification(Request $request)
    {
        $offer = Offer::find($request->id);
        $optionBuilder = new OptionsBuilder();
        $optionBuilder->setTimeToLive(60*20);

        $notificationBuilder = new PayloadNotificationBuilder(trans('api.app_name')); 
        $notificationBuilder->setBody($offer->text)
                            ->setSound('default');
                            
        $dataBuilder = new PayloadDataBuilder();
        
       
        $dataBuilder->addData(['type' =>'offer','service' => $this->getServices()]);
        
        $option = $optionBuilder->build();
        $notification = $notificationBuilder->build();
        $data = $dataBuilder->build();
        
        $tokens = User::where('player_id','!=','')->pluck('player_id')->toArray();
        
        $downstreamResponse = FCM::sendTo($tokens, $option, $notification, $data);
        $downstreamResponse->numberSuccess();
        $downstreamResponse->numberFailure();
        $downstreamResponse->numberModification();
        
        // return Array - you must remove all this tokens in your database
        $downstreamResponse->tokensToDelete();
        
        // return Array (key : oldToken, value : new token - you must change the token in your database)
        $downstreamResponse->tokensToModify();
        
        // return Array - you should try to resend the message to the tokens in the array
        $downstreamResponse->tokensToRetry();
        
        // return Array (key:token, value:error) - in production you should remove from your database the tokens
        $downstreamResponse->tokensWithError();
    }
    // === End function ===

    // === Logout ===
    public function logout()
    {
        Auth::logout();
        return \Redirect::route('login');
    }
    // === End function ===

}
