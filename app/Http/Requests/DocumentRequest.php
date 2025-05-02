<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DocumentRequest extends FormRequest
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
            'type'=>'required|string',
            'titre'=>'required|string',
            'file'=>'required|file|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'medecin_id' => 'nullable|exists:user,id_user',
        ];
    }
}
