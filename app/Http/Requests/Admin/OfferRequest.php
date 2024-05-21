<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class OfferRequest extends FormRequest
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
            'title' => 'required',
            'title_en' => 'required', 
            'image' => 'nullable|mimes:jpg,jpeg,png|max:4096',
            'price' => 'required|numeric|min:1',
            'text' => 'required',
        ];

        return $rules;
    }

    public function messages()
    {
        return [
            'title.regex' => trans('admin.title_error'),
            'title_en.regex' => trans('admin.title_error'),
            'image.mimes' => trans('admin.image_error'),
        ];
    }
}