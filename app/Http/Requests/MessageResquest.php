<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MessageResquest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'content'=> 'required|string',
            'user_id'=> 'required|integer|exists:users,id',
            'chat_ai_id'=> 'required:if chat_ai_id|integer|exists:chat_ai,id',

        ];
    }
}
