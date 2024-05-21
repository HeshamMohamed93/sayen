<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SettingRequest extends FormRequest
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
        return [
            'user_app_android_url' => 'nullable|url',
            'user_app_ios_url' => 'nullable|url',
            'team_app_android_url' => 'nullable|url',
            'team_app_ios_url' => 'nullable|url',
            'about_sayen_shortcut' => 'required',
        ];
    }
}
