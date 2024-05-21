<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\CommonQuestion;
use App\StaticPage;
use App\Service;

class CommonServiceController extends BaseController
{
    // === Get static page ===
    public function getStaticPage($id)
    {
        $page = StaticPage::find($id);
        return $this->respond(['page' => $page]);    
    }
    // === End function ===

    // === Get common question ===
    public function getCommonQuestions()
    {
        $questions = CommonQuestion::all();
        return $this->respond(['questions' => $questions]);    
    }
    // === End function ===

    // === Get all services ===
    public function getServices($id = null)
    {
        if($id)
        {
            return Service::where('id', $id)->pluck('name')[0];
        }
        else
        {
            $services = Service::where('active', '1')->get();
            return $this->respond(['services' => $services]);    
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
    // === End function ===

}
