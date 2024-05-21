<?php

namespace App\Http\Requests\Admin;
use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
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
    public function rules(Request $request)
    {
        $rules = [
            'name' => 'nullable|regex:/^[\pL\s\-]+$/u',
            'email' => 'nullable|email|regex:/(.+)@(.+)\.(.+)/i|unique:users,email,'.$this->route()->user,
            'phone' => 'required|numeric|digits_between:9,15|unique:users,phone,'.$this->route()->user,
            'password' => 'nullable|min:8|max:15',
            'image' => 'nullable|mimes:jpg,jpeg,png|max:4096',
            'excellence_client' => ['required',Rule::in(['1','2'])],
        ];

        if($request->excellence_client == 1)
        {
            $rules['building_id'] = 'required|exists:buildings,id';
            $rules['flat'] = 'required';
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
