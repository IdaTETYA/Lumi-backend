<?php

// app/Http/Requests/ChatMessageRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChatMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'message' => 'required|string|max:1000',
            'chat_ai_id' => 'nullable|uuid|exists:chat_ai,id_chat_ai',
            'title' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'message.required' => 'Le message est requis.',
            'message.string' => 'Le message doit être une chaîne de caractères.',
            'message.max' => 'Le message ne peut pas dépasser 1000 caractères.',
            'chat_ai_id.uuid' => 'L\'ID de la conversation doit être un UUID valide.',
            'chat_ai_id.exists' => 'La conversation spécifiée n\'existe pas.',
            'title.string' => 'Le titre doit être une chaîne de caractères.',
            'title.max' => 'Le titre ne peut pas dépasser 255 caractères.',
        ];
    }
}
