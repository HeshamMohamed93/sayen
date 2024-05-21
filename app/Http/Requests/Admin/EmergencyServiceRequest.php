<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class EmergencyServiceRequest extends FormRequest
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
            'title' => 'required|regex:/^[\pL\s\-]+$/u',
            'title_en' => 'required|regex:/^[\pL\s\-]+$/u', 
        ];

        return $rules;
    }

    public function messages()
    {
        return [
            'title.regex' => trans('admin.name_error'),
            'title_en.regex' => trans('admin.name_error'),
        ];
    }
}