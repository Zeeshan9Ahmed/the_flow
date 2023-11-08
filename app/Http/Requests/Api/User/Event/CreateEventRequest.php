<?php

namespace App\Http\Requests\Api\User\Event;

use Illuminate\Foundation\Http\FormRequest;

class CreateEventRequest extends FormRequest
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
            'title' => 'required',
            'image' => 'required',
            'description' => 'required',
            'time' => 'required',
            'date' => 'required',
            'address' => 'required',
            'zip_code' => 'required',
            'state' => 'required',
            'image' => 'mimes:jpeg,png,jpg,gif'
        ];
    }
}
