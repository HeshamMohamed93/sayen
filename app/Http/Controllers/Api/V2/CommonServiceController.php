<?php

namespace App\Http\Controllers\Api\V2;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\CommonQuestion;
use App\StaticPage;
use App\Service;
use App\User;
use App\Offer;
use App\Setting;
use App\Order;
use App\Transformers\Api\ShowInvoiceTransformer;
use PDF;
use App\EmergencyService;

class CommonServiceController extends BaseController
{
    public function __construct(Request $request)
    {
        $this->middleware('ApiUser')->only(['getServices','getSubServices','getOffers']);
    }
    public function makePdf($id){
        // Fetch all customers from database
        $path = base_path().'/public/uploads/';
        $data = Order::where('id', $id)->first();
        set_time_limit(300);
        ini_set('max_execution_time', '300');
        ini_set("pcre.backtrack_limit", "5000000");
        $html = view('admin.services_pdf',compact('data'))->render(); // file render
        $mpdf = new \Mpdf\Mpdf(['tempDir' => storage_path('/tmp')]);
        $mpdf->WriteHTML($html);
        $mpdf->Output($path.'services_report_'.$data->id.'.pdf','F');
        return response()->json(['path'=>'https://sayen.co/public/uploads/services_report_'.$data->id.'.pdf']);
    }
    
    // === Get static page ===
    public function getStaticPage(Request $request,$id)
    {
        $page = StaticPage::where('lang',($request->header('lang'))?$request->header('lang'):'ar')->first();
        if(isset($page) && ($page->facebook == '' || $page->facebook == null)){
            unset($page['facebook']);
        }
        if(isset($page) && ($page->twitter == '' || $page->twitter == null)){
            unset($page['twitter']);
        }
        if(isset($page) && ($page->instagram == '' || $page->instagram == null)){
            unset($page['instagram']);
        }
        if(isset($page) && ($page->whatsapp == '' || $page->whatsapp == null)){
            unset($page['whatsapp']);
        }
        if(isset($page) && ($page->telegram == '' || $page->telegram == null)){
            unset($page['telegram']);
        }
        if(isset($page) && ($page->phone == '' || $page->phone == null)){
            unset($page['phone']);
        }
        return $this->respond(['page' => $page]);    
    }
    // === End function ===

    // === Get common question ===
    public function getCommonQuestions(Request $request)
    {
        $questions = CommonQuestion::where('lang',($request->header('lang'))?$request->header('lang'):'ar')->get();
        return $this->respond(['questions' => $questions]);    
    }
    // === End function ===

    // === Get common question ===
    public function getOffers(Request $request)
    {   
        $user = User::findOrFail($request->user['id']);
        if(!$request->header('lang') || $request->header('lang') == 'ar'){
            $offers = Offer::where('status',1)->where('service_id',$request->service_id)->select('id','title','price','service_id','from','to','image')->get();
        }else{
            $offers = Offer::where('status',1)->where('service_id',$request->service_id)->select('id','title_en as title','price','service_id','from','to','image')->get();
        }
        if($user->excellence_client == 1){
            $offers = [];
        }
        return $this->respond(['offers' => $offers]);  
    }
    // === End function ===

    // === Get all services ===
    public function getServices(Request $request, $id = null)
    {
        $user = User::findOrFail($request->user['id']);
        //dd($user);
        if($id)
        {
            if(!$request->header('lang') || $request->header('lang') == 'ar'){
                return Service::where('id', $id)->pluck('name')[0];
            }else{
                return Service::where('id', $id)->pluck('name_en AS name')[0];
            }
        }
        else
        {
            if(!$request->header('lang') || $request->header('lang') == 'ar'){
                $services = Service::where('active', '1')->where('parent_id',0)->select('id','name','initial_price_excellence_client','initial_price','active','warranty','deleted_at','image','text','numbers','device_number')->get();
                $emergencyServices = EmergencyService::where('status', '1')->select('id','title')->get();
            }else{
                $services = Service::where('active', '1')->where('parent_id',0)->select('id','name_en as name','initial_price_excellence_client','initial_price','active','warranty','deleted_at','image','text_en as text','numbers','device_number')->get();
                $emergencyServices = EmergencyService::where('status', '1')->select('id','title_en as title')->get();
            }
            foreach($services as $service){
                if($service['text'] == null){
                    $service['text'] = '';
                }
                if($user->excellence_client == 1){
                    $service['initial_price'] = $service->initial_price_excellence_client;
                }
                unset($service['initial_price_excellence_client']);
                $checkOffer = Offer::where('service_id',$service->id)->where('status',1)->first();
                $supservicesIDS = Service::where('active', '1')->where('parent_id',$service->id)->pluck('id')->toArray();
                $checkSubOffer =  Offer::whereIN('service_id',$supservicesIDS)->where('status',1)->first();
                if(($checkOffer || $checkSubOffer) && $user->excellence_client == 2){
                    $service['offer'] = 1;
                }else{
                    $service['offer'] = 0;
                }
                $service['checkSub'] = $service->checkSub();
            }
            foreach($emergencyServices as $key => $emergencyService){
                $emergencyService['reasons'] = $emergencyService->reasons;
                
                
                    foreach($emergencyService->reasons as $reason){
                        if(!$request->header('lang') || $request->header('lang') == 'ar'){
                            $reason['reason'] = $reason->reason;
                        }else{
                            $reason['reason'] = $reason->reason_en;
                        }
                        unset($reason['reason_en']);
                    }
                

                // if(count($emergencyService->reasons)>0){
                //     unset($emergencyServices[$key]);
                // }else{
                //     foreach($emergencyService->reasons as $reason){
                //         if(!$request->header('lang') || $request->header('lang') == 'ar'){
                //             $reason['reason'] = $reason->reason;
                //         }else{
                //             $reason['reason'] = $reason->reason_en;
                //         }
                //         unset($reason['reason_en']);
                //     }
                // }
            }
            if(!$request->header('lang') || $request->header('lang') == 'ar'){
                $offers = Offer::where('status',1)->where('show',1)->with('service')->select('id','title','price','service_id','from','to','image')->get();
                $settings = Setting::select('user_app_android_version','user_app_ios_version','text_emergency')->first();
            }else{
                $offers = Offer::where('status',1)->where('show',1)->with('service')->select('id','title_en as title','price','service_id','from','to','image')->get();
                $settings = Setting::select('user_app_android_version','user_app_ios_version','text_emergency_en as text_emergency')->first();
            }
            if($user->excellence_client == 1){
                $offers = [];
            }
            foreach($offers as $key => $offer){
                unset($offers[$key]['service']['name']);
                unset($offers[$key]['service']['name_en']);
                unset($offers[$key]['service']['initial_price_excellence_client']);
                if(!$request->header('lang') || $request->header('lang') == 'ar'){
                    $offers[$key]['service']['name'] = $offer->Service->name;
                }else{
                    $offers[$key]['service']['name'] = $offer->Service->name_en;
                }
                if($user->excellence_client == 1){
                    $offers[$key]['service']['initial_price'] = $offer->Service->initial_price_excellence_client;
                }
                unset($offer->Service);
                $offer['service']['offer'] = 1;
            }
            //$services = [];
            //$offers = [];
            //$emergencyServices =[];
            return $this->respond(['services' => $services,'offers' => $offers,'user_data' => $user,'setting' =>$settings,'emergencyServices' => $emergencyServices]);    
        }
    }
    // === End function ===

    // === Get sub services ===
    public function getSubServices(Request $request, $id = null)
    {
        $user = User::findOrFail($request->user['id']);
        //dd($user);
        if($id)
        {
            if(!$request->header('lang') || $request->header('lang') == 'ar'){
                return Service::where('id', $id)->pluck('name')[0];
            }else{
                return Service::where('id', $id)->pluck('name_en AS name')[0];
            }
        }
        else
        {
            if(!$request->header('lang') || $request->header('lang') == 'ar'){
                $services = Service::where('active', '1')->where('parent_id',$request->service_id)->select('id','name','initial_price_excellence_client','initial_price','active','warranty','deleted_at','image','text','numbers','device_number')->get();
            }else{
                $services = Service::where('active', '1')->where('parent_id',$request->service_id)->select('id','name_en as name','initial_price_excellence_client','initial_price','active','warranty','deleted_at','image','text_en as text','numbers','device_number')->get();
            }
            foreach($services as $service){
                if($service['text'] == null){
                    $service['text'] = '';
                }
                if($user->excellence_client == 1){
                    $service['initial_price'] = $service->initial_price_excellence_client;
                }
                unset($service['initial_price_excellence_client']);
                $checkSubOffer =  Offer::where('service_id',$service->id)->where('status',1)->first();
                if($checkSubOffer){
                    $service['offer'] = 1;
                }else{
                    $service['offer'] = 0;
                }
                $service['checkSub'] = $service->checkSub();
            }
            return $this->respond(['services' => $services,'user_data' => $user]);    
        }
    }
    // === End function ===

    // === Check user status ===
    public function checkStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_type' => ['required',Rule::in(['1','2'])],
        ]);

        if ($validator->fails()) 
        {
            return $this->respondWithError($validator->messages());
        }

        if($request->user_type == 1)
        {
            config(['auth.defaults.guard' => 'api-users', 'auth.defaults.passwords' => 'users']);
            $member = $this->getAuthenticatedUser();
            $table = 'users';

        }
        else if($request->user_type == 2)
        {
            config(['auth.defaults.guard' => 'api-teams', 'auth.defaults.passwords' => 'teams']);
            $member = $this->getAuthenticatedUser();
            $table = 'teams';
        }

        if($member)
        {
            return $this->respond(['is_ban' => !$member->active, 'status_code' => 200]);
        }
        else
        {
            return $this->respondWithError(trans('api.user_not_exist'));
        }
    }
    public function showInvoice(Request $request,$id,ShowInvoiceTransformer $transformer){
        app()->setLocale($request->header('lang'));
        $order = Order::where('id', $id)->first();

        if($order)
        {
            return $this->respond(['invoice' => $transformer->transform($order), 'status_code' => 200]);
        }
        else
        {
            return $this->respondWithError(trans('api.not_found_order'));
        }
    }
    public function testsendSms(Request $request){
        
        $curl = curl_init();
        $app_id = "7nfgGwaqDEuzlKV9pmN6wVw6x0MWpKkTmOnhnZaF";
        $app_sec = "jU8or1HwwUnL8ZLLaE8pcAxXRWZYPEKKCrU8q8TsDmU7RJ9SnKy0YpMnixo2cEXgaRyUEQdXvAZUVnlMeSMJIQqD6SHH4L9KIdC9";
        $app_hash  = base64_encode("$app_id:$app_sec");
        $messages = [];
        $messages["messages"] = [];
        $messages["messages"][0]["text"] = $request->message;
        $messages["messages"][0]["numbers"][] = $request->phone;
        $messages["messages"][0]["sender"] = "SAYEN.APP";

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api-sms.4jawaly.com/api/v1/account/area/sms/send',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>json_encode($messages),
            CURLOPT_HTTPHEADER => array(
                'Accept: application/json',
                'Content-Type: application/json',
                'Authorization: Basic '.$app_hash
            ),
        ));

        $response = curl_exec($curl);
        $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $response_json = json_decode($response, true);

        if ($status_code == 200) {
            if (isset($response_json["messages"][0]["err_text"])) {
                echo $response_json["messages"][0]["err_text"];
            } else {
                echo "تم الارسال بنجاح  " . " job id:" . $response_json["job_id"];
            }
        } elseif ($status_code == 400) {
            echo $response_json["message"];
        } elseif ($status_code == 422) {
            echo "نص الرسالة فارغ";
        } else {
            echo "محظور بواسطة كلاودفلير. Status code: {$status_code}";
        }
        curl_close($curl);
        return $response;
        $this->sendSms($request->message, $request->phone);
    }
    // === End function ===

}
