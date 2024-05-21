<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Auth;
use Illuminate\Routing\Route;

class AdminRequest extends FormRequest
{
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
    public function rules(Route $route)
    {        
        $value = '';
        $column = '';

        if($this->route()->admin == null)
        {
            $value = Auth::user()->id;
            $column = 'id';
        }
        else
        {
            $value = $this->route()->admin;
            $column = 'email';
        }
        
        $rules = [
            'name' => 'required|regex:/^[\pL\s\-]+$/u', 
            'email' => 'required|email|regex:/(.+)@(.+)\.(.+)/i|unique:admins,'.$column.','.$value,
            'image' => 'nullable|mimes:jpg,jpeg,png|max:4096',
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

