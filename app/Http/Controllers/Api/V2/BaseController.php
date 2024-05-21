<?php

namespace App\Http\Controllers\Api\V2;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use JWTAuth;
use Auth;
use App\User;
use App\Team;
use App\Coupon;
use Carbon\Carbon;
use App\Service;
use App\OrderInvoice;
use DB;

class BaseController extends Controller
{
    protected $statusCode = 200;
    protected $user_status = true;
    protected $coupon_status = true;

    public function respondWithError($message, $status_code = 400)
    {
        $msg = '';

        if(gettype($message) == 'object')
        {
            foreach($message->messages() as $key => $value)
            {
                $msg .= $value[0];
                $msg .= " | ";
            }

            $msg = rtrim($msg, ' | ');
        }
        else
        {
            $msg  = $message;
        }

        return $this->respond(['error' =>  $msg , 'status_code' => $status_code]);
    }

    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    public function respond($data ,$headers =[])
    {
        return response()->json($data,$this->getStatusCode(), $headers);
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    // === Send code via sms ===
    public function sendSms($message, $phone)
    {
        $whitelist = array('127.0.0.1','::1');

        $curl = curl_init();
        $app_id = "7nfgGwaqDEuzlKV9pmN6wVw6x0MWpKkTmOnhnZaF";
        $app_sec = "jU8or1HwwUnL8ZLLaE8pcAxXRWZYPEKKCrU8q8TsDmU7RJ9SnKy0YpMnixo2cEXgaRyUEQdXvAZUVnlMeSMJIQqD6SHH4L9KIdC9";
        $app_hash  = base64_encode("$app_id:$app_sec");
        $messages = [];
        $messages["messages"] = [];
        $messages["messages"][0]["text"] = $message;
        $messages["messages"][0]["numbers"][] = $phone;
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
        curl_close($curl);
        return true;
        /*
        if(!in_array($_SERVER['REMOTE_ADDR'], $whitelist))
        {
            // $username = "altamayouz";
            // $password = "Altamayouz1594";
            // $sender = "ALTAMAYOUZ";
            // $message = str_replace(' ', '%20', $message);
            // $url = "https://www.jawalbsms.ws/api.php/?user=".$username."&pass=".$password."&to=".$phone."&message=".$message."&sender=".$sender."&unicode=u";

            $username = "+966509997657";
            $password = "Sayen@2022";
            $sender = "SAYEN.APP";
            $message = str_replace(' ', '%20', $message);
            $url = "https://www.hisms.ws/api.php?send_sms&username=".$username."&password=".$password."&numbers=".$phone."&message=".$message."&sender=".$sender."&date=".date('Y-m-d')."&time=".date('H:i');


            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch,CURLOPT_TIMEOUT,5000);
            $response = curl_exec($ch);
            $result = json_decode($response);
            return true;
        }
        */
    }
    // === End function ===

    // === Validate user authorization ====
    public function getAuthenticatedUser()
    {
        try
        {
            if (! $user = Auth::user())
            {
                return false;//response()->json(['user_not_found'], 404);
            }
        }
        catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e)
        {
            return response()->json(['token_expired'], $e->getStatusCode());
        }
        catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e)
        {
            return response()->json(['token_invalid'], $e->getStatusCode());
        }
        catch (Tymon\JWTAuth\Exceptions\JWTException $e)
        {
            return response()->json(['token_absent'], $e->getStatusCode());
        }
        return $user;
    }
    // === End function ===

    // === Check user status and ability ===
    public function checkUserStatus($user, $method = null)
    {
        if(!$user)
        {
            $this->user_status = false;
            return $this->respondWithError(trans('api.user_not_exist'));
        }
        else if($user->phone_verified == '0' && $method != 'forget-password')
        {
            $this->user_status = false;
            return $this->respondWithError(trans('api.not_verified_account'), 401);
        }
        else if($user->active == '0')
        {
            $this->user_status = false;
            return $this->respondWithError(trans('api.not_active_account'));
        }
        else
        {
            return $this->user_status = true;
        }
    }
    // === End function ===

    // === Calculate total price after coupon usage ===
    protected function calculateCouponValues($coupon_code, $total_price, $service_id, $new_order = false)
    {
        $coupon_code = arTOen($coupon_code);
        if($new_order)
        {
            $existing_coupon = Coupon::where('code', $coupon_code)->first();
        }
        else
        {
            $existing_coupon = Coupon::where('code', $coupon_code)->withTrashed()->first();
        }
        $return['total_price'] = $total_price;

        if($existing_coupon)    //=== valid coupon
        {
            if($existing_coupon->service_id != 0 && $existing_coupon->service_id != $service_id)
            {
                $return['status'] = false;
                $return['message'] = trans('api.invalid_coupon_with_service');
            }
            else
            {
                $return['status'] = true;
                $return['coupon_id'] = $existing_coupon->id;
                $return['final_price'] = $total_price;

                if($existing_coupon->discount_type == 1)    //=== percentage
                {
                    $return['final_price'] -= ($total_price * $existing_coupon->discount) / 100;
                }
                else    //=== price
                {
                    $return['final_price'] = $total_price - $existing_coupon->discount;

                    if($return['final_price'] < 0)
                    {
                        $return['final_price'] = 0;
                    }
                }

                $return['coupon_discount'] = $total_price - $return['final_price'] ;
            }
        }
        else    //=== not valid coupon
        {
            $return['status'] = false;
            $return['message'] = trans('api.invalid_coupon');
        }

        return $return;
    }
    // === End function ===

    // === Check existing valid coupon ===
    protected function checkCoupon($code, $user_id)
    {
        $code = arTOen($code);
        $coupon = Coupon::where('code', $code)->first();

        if($coupon)
        {
            if(Carbon::now() >= $coupon->date_from   && Carbon::now() <= $coupon->date_to )
            { 
                $this->coupon_status = true;

                $coupon_total_usage = OrderInvoice::where('coupon_id', $coupon->id)->groupBy('coupon_id')->count();

                if($coupon_total_usage >= $coupon->num_of_users)
                {
                    $this->coupon_status = false;
                    return $this->respondWithError(trans('api.not_available_coupon'));
                }

                $coupon_total_usage_per_user = OrderInvoice::with(array('order' => function($query) use($user_id) {
                                                    $query->select('user_id')->where('user_id', $user_id);
                                                }))->where('coupon_id', $coupon->id)->count();

                if($coupon_total_usage_per_user >= $coupon->num_of_usage_per_user)
                {
                    $this->coupon_status = false;
                    return $this->respondWithError(trans('api.max_coupon_usage'));
                }
            }
            else if($coupon->active == 0)
            {
                $this->coupon_status = false;
                return $this->respondWithError(trans('api.not_available_coupon'));
            }
            else
            {
                $this->coupon_status = false;
                return $this->respondWithError(trans('api.coupon_is_expired'));
            }

            return $coupon;
        }
        else
        {
            $this->coupon_status = false;
            return $this->respondWithError(trans('api.invalid_coupon'));
        }
    }
    // === End function ===

    // === Get all services ===
    protected function getServices(Request $request, $id = null)
    {
        if($id)
        {
            if(!$request->header('lang') || $request->header('lang') == 'ar'){
                return Service::where('id', $id)->pluck('name')[0];
            }else{
                return Service::where('id', $id)->pluck('name_en')[0];
            }
        }
        else
        {
            if(!$request->header('lang') || $request->header('lang') == 'ar'){
                $services = Service::where('active', '1')->select('id','name','initial_price','active','warranty','deleted_at','image')->get();
            }else{
                $services = Service::where('active', '1')->select('id','name_en as name','initial_price','active','warranty','deleted_at','image')->get();
            }
            return $this->respond(['services' => $services]);
        }
    }
    // === End function ===
}
