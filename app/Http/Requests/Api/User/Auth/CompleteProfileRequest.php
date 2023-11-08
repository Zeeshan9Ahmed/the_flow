<?php

namespace App\Http\Requests\Api\User\Auth;

use Illuminate\Foundation\Http\FormRequest;

class CompleteProfileRequest extends FormRequest
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
            'full_name' => 'required|min:3',
            'image' => 'nullable|sometimes|mimes:jpeg,png,jpg,gif',
            'cover_image' => 'nullable|sometimes|mimes:jpeg,png,jpg,gif',
            'background_profile' => 'nullable|sometimes|mimes:jpeg,png,jpg,gif',
            'phone' => 'required|numeric',
            'address' => 'required',
            'zip_code' => 'required',
            'state' => 'required',
            'date_of_birth' => 'required'
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        $response =  commonErrorMessage($validator->errors()->first());
        throw new \Illuminate\Validation\ValidationException($validator, $response);
    }
}
