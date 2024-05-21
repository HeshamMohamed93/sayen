<?php

namespace App\Http\Controllers\Api\V1\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\V1\BaseController;
use App\ContactUs;

class ContactUsController extends BaseController
{
    public function __construct(Request $request)
    {
        $this->middleware('ApiUser');
    }

    // === Send contact us message ===
    public function sendMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'message' => 'required',
        ]);

        if ($validator->fails()) 
        {
            return $this->respondWithError($validator->messages());
        }

        ContactUs::create([
            'user_id' =>$request->user['id'],
            'name' => $request->name,
            'message' => $request->message,
        ]);

        return $this->respond(['message' => trans('api.success_send'),'status_code' => 200]); 
    }
    // === End function ===
    
}
