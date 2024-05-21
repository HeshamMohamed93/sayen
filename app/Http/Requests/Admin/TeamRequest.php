<?php

namespace App\Http\Requests\Admin;
use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Routing\Route;

class TeamRequest extends FormRequest
{


    function __construct(Request $request)
    {
        $request['phone'] = PhoneFormateForDB($request->phone, 966);
    }
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(Request $request, Route $route)
    { 
        $rules = [
            'name' => 'required|regex:/^[\pL\s\-]+$/u', 
            'image' => 'nullable|mimes:jpg,jpeg,png|max:4096',
            'phone' => 'required|numeric|digits_between:9,15|unique:teams,phone,'.$this->route()->team,
            'service_id' => 'required|exists:services,id',
            'email' => 'nullable|email|regex:/(.+)@(.+)\.(.+)/i|unique:users,email,'.$this->route()->team,
        ];

        if($route->getActionMethod() == 'store')
        {
            $rules['password'] = 'required|min:8';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'name.regex' => trans('admin.name_error'),
            'image.mimes' => trans('admin.image_error'),
        ];
    }
}
