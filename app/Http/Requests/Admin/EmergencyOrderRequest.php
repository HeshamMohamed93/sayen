<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmergencyOrderRequest extends FormRequest
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
            // 'service_id' => 'required|integer|exists:services,id',
            'team_id' => 'nullable|integer|exists:teams,id',
            'status' => ['required',Rule::in(['1','2', '3', '4', '5'])],
            'visit_date' => 'required'
        ];

        return $rules;
    }

    // public function messages()
    // {
    //     // return [
    //     //     'team_id.required' => trans('admin.no_assign_team'),
    //     // ];
    // }
}
