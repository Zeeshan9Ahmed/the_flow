<?php

namespace App\Http\Requests\Api\User\Music;

use Illuminate\Foundation\Http\FormRequest;

class AssignGenereRequest extends FormRequest
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
            'genere_id' => 'required|numeric'
        ];
    }
}
