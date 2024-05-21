<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CouponRequest extends FormRequest
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
            'code' => 'required|unique:coupons,code,'.$this->route('coupon'),
            'discount' => 'required|numeric|min:0',
            'discount_type' => ['required',Rule::in(['1','2'])],
            'num_of_users' => 'required|integer|min:1',
            'num_of_usage_per_user' => 'required|integer|min:1',
            'service_id' => 'required|numeric',
        ];

        return $rules;
    }
}

