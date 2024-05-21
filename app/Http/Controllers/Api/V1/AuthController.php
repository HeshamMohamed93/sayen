<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use JWTAuth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\User;
use App\Team;
use App\VerifyOTP;
use Carbon\Carbon;
use DB;
use App\ChangePhoneRequest;
use Illuminate\Support\Facades\Hash;

class AuthController extends BaseController
{
    // === Login user ===
    public function userLogin(Request $request)
    {
        $validator = Validator::make( $request->all(), [
            'phone' => 'required',
            'password' => 'required',
            'country_code' => 'required',
        ]);
        
        if ($validator->fails()) 
        {
            return $this->respondWithError($validator->messages());
        }
        else
        {
            $phone = PhoneFormateForDB($request->phone, $request->country_code);
            
            if(!$phone)
            {
                return $this->respondWithError(trans('api.invalid_phone'));
            }
            else
            {
                $credentials = array("phone" => $phone, "password" => $request->password);
                
                if(!$token = auth('api-users')->attempt($credentials))
                {
                    return $this->respondWithError(trans('api.check_your_credentials'));
                }
                else
                {
                    $user = User::where('phone', $phone)->first();
                    $check_user =  $this->checkUserStatus($user);

                    if($this->user_status == true)
                    {
                        $user['phone'] = '0'.substr($user->phone, 3);
                        return $this->respond(['token' =>  $token, 'user' => $user, 'status_code' => 200]);
                    }
                    else
                    {
                        return $check_user;
                    } 
                }
            }
        }
    }
    // === End function ===

    // === Register new account for user ===
    public function userRegister(Request $request)
    {
        $validator = Validator::make( $request->all(), [
            'phone' => 'required|max:15',
            'country_code' => 'required',
            'password' => 'required',
            'excellence_client' => ['required',Rule::in(['1','2'])],
        ]);

        if ($validator->fails()) 
        {
            return $this->respondWithError($validator->messages());
        }
        else
        {
            $phone = PhoneFormateForDB($request->phone, $request->country_code);
            
            if(!$phone)
            {
                return $this->respondWithError(trans('api.invalid_phone'));
            }
            else
            {
                $user = User::where('phone', $phone)->first();
                
                if($user)
                {
                    return $this->respondWithError(trans('api.mobile_is_existing'));
                }
                else
                {
                    $request['phone'] = $phone;
                    $request['active'] = '1';
                    $request->except('country_code');
                    $save_user = User::create($request->all());

                    if(!$save_user)
                    {
                        return $this->respondWithError(trans('api.not_saved'));
                    }
                    else
                    {
                        $create_otp = $this->createOTP($save_user->id, '1', '1', trans('api.activation_code_msg'));
                        
                        if($create_otp)
                        {
                            return $this->respond(['message' => trans('api.success_account_creation'), 'status_code' => 200]);
                        }
                        else
                        {
                            User::find($save_user->id)->delete();
                            return $this->respondWithError(trans('api.not_saved'));
                        }
                    }
                }
            }
        }
    }
    // === End function ===

    // === Login technical team ===
    public function teamLogin(Request $request)
    {
        config(['auth.defaults.guard' => 'api-teams', 'auth.defaults.passwords' => 'teams']);

        $validator = Validator::make( $request->all(), [
            'phone' => 'required',
            'password' => 'required',
            'country_code' => 'required',
        ]);
        
        if ($validator->fails()) 
        {
            return $this->respondWithError($validator->messages());
        }
        else
        {
            $phone = PhoneFormateForDB($request->phone, $request->country_code);
            
            if(!$phone)
            {
                return $this->respondWithError(trans('api.invalid_phone'));
            }
            else
            {
                $team = Team::where('phone', $phone)->first();
                $check_user =  $this->checkUserStatus($team);

                if($this->user_status == true)
                {
                    $credentials = array("phone" => $phone, "password" => $request->password);
                    
                    if(!$token = auth('api-teams')->attempt($credentials)) 
                    {
                        return $this->respondWithError(trans('api.check_your_credentials'));
                    }
                    else
                    {
                        $team['phone'] = '0'.substr($team->phone, 3);
                        return $this->respond(['token' => $token, 'user' => $team, 'status_code' => 200]);
                    }
                }
                else
                {
                    return $check_user;
                }
            }
        }
    }
    // === End function ===

    // === Forget password ===
    public function forgetPassword(Request $request)
    {
        $validator = Validator::make( $request->all(), [
            'phone' => 'required',
            'country_code' => 'required',
            'user_type' => ['required',Rule::in(['1','2'])],
        ]);
        
        if ($validator->fails()) 
        {
            return $this->respondWithError($validator->messages());
        }
        else
        {
            $phone = PhoneFormateForDB($request->phone, $request->country_code);
            
            if(!$phone)
            {
                return $this->respondWithError(trans('api.invalid_phone'));
            }
            else
            {
                $member;

                if($request->user_type == 1) //=== user
                {
                    $member = User::where('phone', $phone)->first();  
                }
                else if($request->user_type == 2) //=== team
                {
                    $member = Team::where('phone', $phone)->first();
                }

                $check_user = $this->checkUserStatus($member, 'forget-password');

                if($this->user_status == true)
                {
                    $create_otp = $this->createOTP($member->id, $request->user_type, '2', trans('api.reset_password_code_msg'));

                    if($create_otp)
                    {
                        return $this->respond(['message' => trans('api.code_is_sent'), 'status_code' => 200]); 
                    }
                    else
                    {
                        return $this->respondWithError(trans('api.not_saved'));
                    }
                }
                else
                {
                    return $check_user;
                }
            }
        }
    }
    // === End function === 

    // === Change password when forgetting the old password ===
    public function changePassword(Request $request)
    {
        $validator = Validator::make( $request->all(), [
            'user_type' => ['required',Rule::in(['1','2'])],
            'password' => 'required',
            'old_password' => 'nullable',
        ]);

        if ($validator->fails()) 
        {
            return $this->respondWithError($validator->messages());
        }
        else
        {
            $member;
            $table;

            if($request->user_type == 1)    //=== user
            {
                config(['auth.defaults.guard' => 'api-users', 'auth.defaults.passwords' => 'users']);
                $member = $this->getAuthenticatedUser();
                $table = 'users';
            }
            else if($request->user_type == 2)    //=== team
            {
                config(['auth.defaults.guard' => 'api-teams', 'auth.defaults.passwords' => 'teams']);
                $member = $this->getAuthenticatedUser();
                $table = 'teams';
            }

            if(!$member)
            {
                return $this->respondWithError(trans('api.user_not_exist')); 
            }

            $check_user = $this->checkUserStatus($member, 'forget-password');
            
            if($this->user_status == true)
            {
                if($request->old_password)
                {
                    if (!Hash::check($request->old_password, $member->password)) 
                    {
                        return $this->respondWithError(trans('api.invalid_old_password'));
                    }
                }
                
                $change_password = DB::table($table)->where('id', $member->id)->update(array('password' => bcrypt($request->password)));
            
                if($change_password)
                {
                    return $this->respond(['message' => trans('api.suucess_update') ,'status_code' => 200]);
                }
                else
                {
                    return $this->respondWithError(trans('api.invalid_update'));
                }
            }
            else
            {
                return $check_user;
            }
        }
    }
    // === End function === 

    // === Verify code for both activation and reset password ===
    public function verifyCode(Request $request)
    {
        $validator = Validator::make( $request->all(), [
            'phone' => 'required',
            'country_code' => 'required',
            'user_type' => ['required',Rule::in(['1','2'])],
            'code_type' => ['required',Rule::in(['1','2', '3'])],
            'code' => 'required|numeric'
        ]);

        if ($validator->fails()) 
        {
            return $this->respondWithError($validator->messages());
        }
        else
        {
            $phone = PhoneFormateForDB($request->phone, $request->country_code);
            
            if(!$phone)
            {
                return $this->respondWithError(trans('api.invalid_phone'));
            }
            else
            {
                $member;
                $token;

                if($request->user_type == 1 && ($request->code_type == 1 || $request->code_type == 2)) // === user
                {
                    $member = User::where('phone', $phone)->first();
                    $token = auth('api-users')->login($member);
                }
                else if($request->user_type == 2 && ($request->code_type == 1 || $request->code_type == 2))    // === team
                {
                    $member = Team::where('phone', $phone)->first();
                    $token = auth('api-teams')->login($member);
                }
                else if($request->user_type == 1 && $request->code_type == 3)   //=== user verify change phone
                {
                    config(['auth.defaults.guard' => 'api-users', 'auth.defaults.passwords' => 'users']);
                    $member = $this->getAuthenticatedUser();
                    $table = 'users';
                }
                else if($request->user_type == 2 && $request->code_type == 3)   //=== team verify change phone
                {
                    config(['auth.defaults.guard' => 'api-teams', 'auth.defaults.passwords' => 'teams']);
                    $member = $this->getAuthenticatedUser();
                    $table = 'teams';
                }
                
                if($member)
                {
                    if($request->code_type == 1)    // === verification
                    {
                        if($member->phone_verified == 1)
                        {
                            return $this->respondWithError(trans('api.account_already_verified'));
                        }
                        else
                        {
                            $otp = VerifyOTP::where([['user_id', $member->id], ['code', arTOen($request->code)], 
                                                        ['user_type', $request->user_type], ['code_type', $request->code_type], 
                                                        ['verified', '0'], ['expired_at', '>=', Carbon::now()]])->first();
                            
                            if($otp)
                            {
                                $otp->verified = '1';
                                $otp->save();

                                $member->phone_verified = '1';
                                $member->save();
                                             
                                return $this->respond(['token' => $token, 'user' => $member, 'status_code' => 200]);
                            }
                            else
                            {
                                return $this->respondWithError(trans('api.invalid_code'));
                            }
                        }
                    }
                    else if($request->code_type == 2)    // === reset password
                    {
                        if($member->phone_verified == 1 || $member->phone_verified == 0)
                        {
                            if($member->active == 1)
                            {
                                $otp = VerifyOTP::where([['user_id', $member->id], ['code', $request->code], 
                                                            ['user_type', $request->user_type], ['code_type', $request->code_type], 
                                                            ['verified', '0'], ['expired_at', '>=', Carbon::now()]])->first();
                                
                                if($otp)
                                {
                                    $otp->verified = '1';
                                    $otp->save();
                                    
                                    return $this->respond(['token' => $token, 'user' => $member, 'status_code' => 200]);
                                }
                                else
                                {
                                    return $this->respondWithError(trans('api.invalid_code'));
                                }
                            }
                            else
                            {
                                return $this->respondWithError(trans('api.not_active_account'));
                            }
                        }
                        else
                        {
                            return $this->respondWithError(trans('api.not_verified_account'));
                        }
                    }
                    else if($request->code_type == 3)   //=== change phone
                    {
                        $otp = VerifyOTP::where([['user_id', $member->id], ['code', $request->code], 
                                                    ['user_type', $request->user_type], ['code_type', $request->code_type], 
                                                    ['verified', '0'], ['expired_at', '>=', Carbon::now()]])->first();

                        $change_phone_request = ChangePhoneRequest::where([['phone', $phone], ['user_id', $member->id], 
                                                    ['user_type', $request->user_type]])->first();

                        if($otp && $change_phone_request)
                        {
                            $otp->verified = '1';
                            $otp->save();

                            $change_phone_request->phone_verified = '1';
                            $change_phone_request->save();

                            $member->phone = $phone;
                            $member->save();
                            
                            return $this->respond(['message' => trans('api.phone_success_change'), 'status_code' => 200]);
                        }
                        else
                        {
                            return $this->respondWithError(trans('api.invalid_code'));
                        }

                    }
                }
                else
                {
                    return $this->respondWithError(trans('api.user_not_exist'));
                }
            }
        }
    }
    // === End function === 

    // === Update user profile ===
    public function userUpdateProfile(Request $request)
    {
        config(['auth.defaults.guard' => 'api-users', 'auth.defaults.passwords' => 'users']);
        $user = $this->getAuthenticatedUser();

        if(!$user)
        {
            return $this->respondWithError(trans('api.user_not_exist')); 
        }

        $validator = Validator::make( $request->all(), [
            'name' => 'required|max:100',
            'email' => 'nullable|email|max:50|unique:users,email,'.$user->id,
            'excellence_client' => ['required',Rule::in(['1','2'])],
        ]);
        
        if ($validator->fails()) 
        {
            return $this->respondWithError($validator->messages());
        }
        else
        {
            $check_user = $this->checkUserStatus($user);
            
            if($this->user_status == true)
            {
                $user = User::find($user->id);
                $user->fill($request->all());
                $update_status = $user->save();
                
                if($update_status)
                {
                    return $this->respond(['message' => trans('api.suucess_update'), 'user' => $user,'status_code' => 200]);
                }
                else
                {
                    return $this->respondWithError(trans('api.invalid_update'));
                }
            }
            else
            {
                return $check_user;
            }
        }
    }
    // === End function === 

    // === Update team profile ===
    public function teamUpdateProfile(Request $request)
    {
        config(['auth.defaults.guard' => 'api-teams', 'auth.defaults.passwords' => 'teams']);
        $team = $this->getAuthenticatedUser();

        if(!$team)
        {
            return $this->respondWithError(trans('api.user_not_exist')); 
        }

        $validator = Validator::make( $request->all(), [
            'name' => 'required|max:100',
            'email' => 'nullable|email|max:50|unique:users,email,'.$team->id,
        ]);
        
        if ($validator->fails()) 
        {
            return $this->respondWithError($validator->messages());
        }
        else
        {
            $check_user = $this->checkUserStatus($team);
            
            if($this->user_status == true)
            {
                $team = Team::find($team->id);
                $team->fill($request->all());
                $update_status = $team->save();
                
                if($update_status)
                {
                    return $this->respond(['message' => trans('api.suucess_update'), 'user' => $team ,'status_code' => 200]);
                }
                else
                {
                    return $this->respondWithError(trans('api.invalid_update'));
                }
            }
            else
            {
                return $check_user;
            }
        }
    }
    // === End function === 

    // === Create notification player id for user or team ===
    public function createPlayerID(Request $request)
    {
        $validator = Validator::make( $request->all(), [
            'player_id' => 'required',
            'user_type' => ['required',Rule::in(['1','2'])],
        ]);
        
        if ($validator->fails()) 
        {
            return $this->respondWithError($validator->messages());
        }
        else
        {
            if($request->user_type == 1)    //=== user
            {
                config(['auth.defaults.guard' => 'api-users', 'auth.defaults.passwords' => 'users']);
                $member = $this->getAuthenticatedUser();
                $table = 'users';
            }
            else if($request->user_type == 2)    //=== team
            {
                config(['auth.defaults.guard' => 'api-teams', 'auth.defaults.passwords' => 'teams']);
                $member = $this->getAuthenticatedUser();
                $table = 'teams';
            }

            if(!$member)
            {
                return $this->respondWithError(trans('api.user_not_exist'));         
            }
            else
            {
                $check_user = $this->checkUserStatus($member);

                if($this->user_status == true)
                {
                    $set_player_id = DB::table($table)->where('id', $member->id)->update(array('player_id' => $request->player_id));
                    return $this->respond(['message' => trans('api.suucess_update') ,'status_code' => 200]);
                }
                else
                {
                    return $this->respondWithError(trans('api.invalid_update'));
                }
            }
        }
    }
    // === End function === 

    //=== Upload team or user image === 
    public function updateProfilePicture( Request $request )
    {
        $validator = Validator::make( $request->all(), [
            'image' => 'required|max:10000',
            'user_type' => ['required',Rule::in(['1','2'])],
        ]);
        
        if ($validator->fails()) 
        {
            return $this->respondWithError($validator->messages());
        }
        else
        {
            if($request->user_type == 1)    //=== user
            {
                config(['auth.defaults.guard' => 'api-users', 'auth.defaults.passwords' => 'users']);
                $member = $this->getAuthenticatedUser();
                $table = 'users';
            }
            else if($request->user_type == 2)    //=== team
            {
                config(['auth.defaults.guard' => 'api-teams', 'auth.defaults.passwords' => 'teams']);
                $member = $this->getAuthenticatedUser();
                $table = 'teams';
            }
            
            if(!$member)
            {
                return $this->respondWithError(trans('api.user_not_exist'));         
            }

            $check_user = $this->checkUserStatus($member);
            
            if($this->user_status == true)
            {
                $image = uploadImage($request->image, $table);

                if($image)
                {
                    if($member->image != null)
                    {
                        deleteImage($member->image, $table);
                    }
                    
                    $change_image = DB::table($table)->where('id', $member->id)->update(array('image' => $image));

                    if($change_image)
                    {
                        return $this->respond(['message' => trans('api.suucess_update'), 'user' => $member ,'status_code' => 200]);
                    }
                    else
                    {
                        return $this->respondWithError(trans('api.invalid_update'));
                    }
                }
                else
                {
                    return $this->respondWithError(trans('api.invalid_update'));
                }
            }
            else
            {
                return $this->respondWithError(trans('api.invalid_update'));
            }
        }
    }
    // === End function === 

    // === Resend code ===
    public function resendCode(Request $request)
    {
        $validator = Validator::make( $request->all(), [
            'phone' => 'required',
            'country_code' => 'required',
            'user_type' => ['required',Rule::in(['1','2'])],
            'code_type' => ['required',Rule::in(['1','2', '3'])],
        ]);
        
        if ($validator->fails()) 
        {
            return $this->respondWithError($validator->messages());
        }
        else
        {
            $phone = PhoneFormateForDB($request->phone, $request->country_code);
            
            if(!$phone)
            {
                return $this->respondWithError(trans('api.invalid_phone'));
            }
            else
            {
                $member;

                if($request->user_type == 1 && ($request->code_type == 1 || $request->code_type == 2)) //=== user (rest password or activation)
                {
                    $member = User::where('phone', $phone)->first();
                }
                else if($request->user_type == 2 && ($request->code_type == 1 || $request->code_type == 2)) //=== team (rest password or activation)
                {
                    $member = Team::where('phone', $phone)->first();
                }

                else if($request->code_type == 3)  //=== change phone
                {
                    $member = ChangePhoneRequest::where([['user_type', $request->user_type], ['phone', $phone]])->first();
                    
                    $existing_valid_otp = VerifyOTP::where([['user_id', $member->user_id], ['user_type', $request->user_type], 
                                                            ['code_type', $request->code_type], ['verified', '0'], 
                                                            ['expired_at', '>=', Carbon::now()]])->first();    
                    
                    if($existing_valid_otp)
                    {
                        $this->sendSms(trans('api.change_phone_code'), $member->phone);
                        $existing_valid_otp->sent_times = $existing_valid_otp->sent_times + 1;
                        $existing_valid_otp->save();
                        return $this->respond(['message' => trans('api.code_is_sent'), 'status_code' => 200]);
                    }
                    else
                    {
                        $create_otp = $this->createOTP($member->id, $request->user_type, $request->code_type, trans('api.change_phone_code'));
                        if($create_otp)
                        {
                            return $this->respond(['message' => trans('api.code_is_sent'), 'status_code' => 200]);
                        }
                        else
                        {
                            return $this->respondWithError(trans('api.not_saved'));
                        }
                    }
                }
                
                if($member)
                {
                    if($member->phone_verified == 1 && $request->code_type == 1)
                    {
                        return $this->respondWithError(trans('api.account_already_verified'));   
                    }
                    else
                    {
                        $check_user = $this->checkUserStatus($member);

                        if($this->user_status == true || ($member->phone_verified == 0 && $request->code_type == 1) || ($member->phone_verified == 0 && $request->code_type == 2))
                        {
                            $message;

                            if($request->code_type == 1)    //=== activation code
                            {
                                $message = trans('api.activation_code_msg');
                            }
                            else if($request->code_type == 2)   //=== reset code
                            {
                                $message = trans('api.reset_password_code_msg');
                            }
                            else if($request->code_type == 3)   //=== change phone
                            {
                                $message = trans('api.change_phone_code');
                            }

                            $existing_valid_otp = VerifyOTP::where([['user_id', $member->id], ['user_type', $request->user_type], 
                                                                        ['code_type', $request->code_type], ['verified', '0'], 
                                                                        ['expired_at', '>=', Carbon::now()]])->first();

                            if($existing_valid_otp)
                            {
                                $message .= $existing_valid_otp->code;
                                $this->sendSms($message, $member->phone);
                                $existing_valid_otp->sent_times = $existing_valid_otp->sent_times + 1;
                                $existing_valid_otp->save();
                                return $this->respond(['message' => trans('api.code_is_sent') ,'status_code' => 200]);
                            }
                            else
                            {
                                $create_otp = $this->createOTP($member->id, $request->user_type, $request->code_type, $message);

                                if($create_otp)
                                {
                                    return $this->respond(['message' => trans('api.code_is_sent') ,'status_code' => 200]);                            }
                                else
                                {
                                    return $this->respondWithError(trans('api.not_saved'));
                                }
                            }
                        }
                        else
                        {
                            return $check_user;
                        }    
                    }
                }
                else
                {
                    return $this->respondWithError(trans('api.user_not_exist'));
                }
            }
        }
    }
    // === End function ===

    // === Change phone number ===
    public function changePhone(Request $request)
    {
        $validator = Validator::make( $request->all(), [
            'phone' => 'required',
            'user_type' => ['required',Rule::in(['1','2'])],
            'country_code' => 'required'
        ]);

        if ($validator->fails()) 
        {
            return $this->respondWithError($validator->messages());
        }
        else
        {
            $member;
            $table;

            if($request->user_type == 1) // === user
            {
                config(['auth.defaults.guard' => 'api-users', 'auth.defaults.passwords' => 'users']);
                $member = $this->getAuthenticatedUser();
                $table = 'users';
            }
            else if($request->user_type == 2)    // === team
            {
                config(['auth.defaults.guard' => 'api-teams', 'auth.defaults.passwords' => 'teams']);
                $member = $this->getAuthenticatedUser();
                $table = 'teams';
            }

            if($member)
            {
                $check_user = $this->checkUserStatus($member);
                
                if($this->user_status == true)
                {
                    $request['phone'] = PhoneFormateForDB($request->phone, $request->country_code);
            
                    if(!$request->phone)
                    {
                        return $this->respondWithError(trans('api.invalid_phone'));
                    }
                    else
                    {
                        $existing_phone = DB::table($table)->where([['phone', $request->phone], ['deleted_at', NULL]])->first();

                        if($existing_phone)
                        {
                            return $this->respondWithError(trans('api.mobile_is_existing'));
                        }
                        else
                        {
                            $request['user_id'] = $member->id;

                            $existing_request = ChangePhoneRequest::where([['phone', $request->phone], ['user_id', $member->id], ['user_type', $request->user_type]])->first();

                            if($existing_request)
                            {
                                $request['code_type'] = '3';
                                return $this->resendCode($request);
                            }
                            else
                            {
                                $save_change_phone_request = ChangePhoneRequest::create($request->all());
                            
                                if(!$save_change_phone_request)
                                {
                                    return $this->respondWithError(trans('api.not_saved'));
                                }
                                else
                                {
                                    $create_otp = $this->createOTP($member->id, $request->user_type, '3', trans('api.change_phone_code'));
                                    
                                    if($create_otp)
                                    {
                                        return $this->respond(['message' => trans('api.code_is_sent'), 'status_code' => 200]);
                                    }
                                    else
                                    {
                                        ChangePhoneRequest::find($save_change_phone_request->id)->delete();
                                        return $this->respondWithError(trans('api.not_saved'));
                                    }        
                                }    
                            }
                        }    
                    }
                }
            }
            else
            {
                return $this->respondWithError(trans('api.user_not_exist'));
            }
        }
    }
    // === End function ===

    // === Get profile ===
    public function profile(Request $request)
    {
        $validator = Validator::make( $request->all(), [
            'user_type' => ['required',Rule::in(['1','2'])]
        ]);
        
        if ($validator->fails()) 
        {
            return $this->respondWithError($validator->messages());
        }
        else
        {
            $member;

            if($request->user_type == 1) // === user
            {
                config(['auth.defaults.guard' => 'api-users', 'auth.defaults.passwords' => 'users']);
                $member = $this->getAuthenticatedUser();
            }
            else if($request->user_type == 2)    // === team
            {
                config(['auth.defaults.guard' => 'api-teams', 'auth.defaults.passwords' => 'teams']);
                $member = $this->getAuthenticatedUser();
            }
        }

        if($member)
        {
            $member['phone'] = '0'.substr($member->phone, 3);
            return $this->respond(['user' => $member, 'status_code' => 200]);
        }
        else
        {
            return $this->respondWithError(trans('api.user_not_exist')); 
        }
    }
    // === End function ===

    // === Logout user or team ===    
    public function logout(Request $request)
    {
        $validator = Validator::make( $request->all(), [
            'user_type' => ['required',Rule::in(['1','2'])]
        ]);

        if ($validator->fails()) 
        {
            return $this->respondWithError($validator->messages());
        }
        else
        {
            $member;

            if($request->user_type == 1) // === user
            {
                config(['auth.defaults.guard' => 'api-users', 'auth.defaults.passwords' => 'users']);
                $member = $this->getAuthenticatedUser();
                $table = 'users';
            }
            else if($request->user_type == 2)    // === team
            {
                config(['auth.defaults.guard' => 'api-teams', 'auth.defaults.passwords' => 'teams']);
                $member = $this->getAuthenticatedUser();
                $table = 'teams';
            }

            if($member)
            {
                auth()->logout();
                DB::table($table)->where('id', $member->id)->update(array('player_id' => null));
                return $this->respond(['message' => trans('api.success_logout'), 'status_code' => 200]);
            }
            else
            {
                return $this->respondWithError(trans('api.user_not_exist')); 
            }
        }      
    }
    // === End Function ===

    // === Create OTP code ===
    public function createOTP($id, $member_type, $code_type, $message)
    {
        $existing_member;

        if($member_type == 1)   // === user
        {
            $existing_member = User::find($id);
        }
        else if($member_type == 2)  // === team
        {
            $existing_member = Team::find($id);
        }
        else
        {
            return $this->respondWithError(trans('api.invalid_type'));
        }

        if($existing_member)
        {
            $code = rand(1000,9999);
            
            $save_code = VerifyOTP::create([
                'user_id' => $id,
                'code' => $code ,
                'user_type' => $member_type,
                'code_type' => $code_type,
                'expired_at' => Carbon::now()->addDays(1)->toDateTimeString(),
                'sent_times' => 1,
            ]);

            if($save_code)
            {
                $message .= $code;
                $this->sendSms($message, $existing_member->phone);
                return true;
            }
        }
        else
        {
            return $this->respondWithError(trans('api.user_not_exist'));
        }
    }
    // === End function ===

    // === Update team location ===
    public function updateLocation(Request $request)
    {
        config(['auth.defaults.guard' => 'api-teams', 'auth.defaults.passwords' => 'teams']);
        $team = $this->getAuthenticatedUser();

        if(!$team)
        {
            return $this->respondWithError(trans('api.user_not_exist')); 
        }

        $validator = Validator::make( $request->all(), [
            'lat' => 'required',
            'lng' => 'required',
        ]);
        
        if ($validator->fails()) 
        {
            return $this->respondWithError($validator->messages());
        }
        else
        {
            $check_user = $this->checkUserStatus($team);
            
            if($this->user_status == true)
            {
                $team = Team::find($team->id);
                $team->fill($request->all());
                $update_status = $team->save();
                
                if($update_status)
                {
                    return $this->respond(['message' => trans('api.suucess_update'), 'location' => $request->all() ,'status_code' => 200]);
                }
                else
                {
                    return $this->respondWithError(trans('api.invalid_update'));
                }
            }
            else
            {
                return $check_user;
            }
        }
    }
    // === End function ===
}
