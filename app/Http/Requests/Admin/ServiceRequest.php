<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ServiceRequest extends FormRequest
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
    public function rules()
    {        
        $rules = [
            'name' => 'required',
            'name_en' => 'required', 
            'image' => 'nullable|mimes:jpg,jpeg,png|max:4096',
            'warranty' => 'required|numeric',
            'initial_price' => 'required|numeric|min:1',
        ];

        return $rules;
    }

    public function messages()
    {
        return [
            'name.regex' => trans('admin.name_error'),
            'name_en.regex' => trans('admin.name_error'),
            'image.mimes' => trans('admin.image_error'),
        ];
    }
}