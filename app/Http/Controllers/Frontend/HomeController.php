<?php

namespace App\Http\Controllers\Frontend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use DB;
use App\Feature;
use App\Setting;
use App\StaticPage;
use App\ContactUs;
use App\User;

class HomeController extends Controller
{
    public function index()
    {
        $general_setting = Setting::first();
        $about = StaticPage::find(1);
        $all_features = Feature::get()->toArray();
        $slider_indicators = ceil(count($all_features) / 3);
        $features = array_chunk($all_features, 3); 
            
        return view('frontend.index', compact('general_setting', 'about', 'features', 'slider_indicators'));
    }
    public function delete_account_request()
    { 
        return view('frontend.deleteAccount');
    }
    public function post_delete_account_request(Request $request){

        $validator = Validator::make( $request->all(), [
            'phone' => 'required',
            'password' => 'required',
            'name' => 'required',
        ]);

        if ($validator->fails())
        {
            session()->flash('messageDeleted', 'Data required');
            return back();
        }
        else
        {

            $credentials = array("phone" => $request->phone, "password" => $request->password);

            if(!$token = auth('api-users')->attempt($credentials))
            {
                session()->flash('messageDeleted', 'Check Your Data');
                return back();
            }
            else
            {
                $user = User::where('phone',$request->phone)->first();
                $user->delete();
                session()->flash('messageDeleted', 'Successfully Deleted! ');
                return back();
            }
        }
    }
    // === Send contact us message ===
    public function sendMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'message' => 'required',
            'email' => 'required|email'
        ]);

        if ($validator->fails()) 
        {
            return response()->json(['errors' => $validator->messages()], 401);
        }

        ContactUs::create([
            'name' => $request->name,
            'message' => $request->message,
            'email' => $request->email,
            'send_from' => '2',
        ]);

        return response()->json(['redirect' => route('index') , 'message' => trans('frontend.success_send')], 200);

    }
    // === End function ===
    public function privacyPolicy(){
        return view('admin.privacyPolicy');
    }
}