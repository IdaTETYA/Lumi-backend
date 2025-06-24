<?php

// app/Http/Requests/ChatMessageRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChatMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'message' => 'required|string|max:1000',
            'chat_ai_id' => 'nullable|uuid|exists:chat_ai,id_chat_ai',
            'title' => 'required_if:chat_ai_id,null|string|max:255',
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
            'title.required_if' => 'Le titre est requis pour une nouvelle conversation.',
            'title.string' => 'Le titre doit être une chaîne de caractères.',
            'title.max' => 'Le titre ne peut pas dépasser 255 caractères.',
        ];
    }
}
