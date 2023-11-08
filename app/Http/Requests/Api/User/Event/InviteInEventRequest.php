<?php

namespace App\Http\Requests\Api\User\Event;

use Illuminate\Foundation\Http\FormRequest;

class InviteInEventRequest extends FormRequest
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
            'user_id' => 'required|numeric',
            'event_id' => 'required|numeric'
        ];
    }
}
