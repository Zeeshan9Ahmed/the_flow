<?php

namespace App\Http\Requests\Api\User\Post;

use Illuminate\Foundation\Http\FormRequest;

class CommentOnPostRequest extends FormRequest
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
            'post_id' => 'required|numeric',
            'parent_id' => 'sometimes|required|numeric',
            'comment' => 'required'
        ];
    }
}
