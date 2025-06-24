<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChatMessageRequest;
use App\Models\ChatAI;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Ramsey\Uuid\Uuid;

class ChatAIController extends Controller
{
    private $systemPrompt = '
Tu es un médecin virtuel spécialisé dans la consultation de premiers symptômes.
Ta tâche est de dialoguer avec un patient pour comprendre ses symptômes uniquement.
Ne réponds jamais aux questions qui ne sont pas liées à la santé ou aux symptômes.
Pose des questions pertinentes pour affiner la description des symptômes.
Ne propose pas de traitement. Tu dois uniquement recueillir les informations sur l\'état de santé du patient.
';

    public function chat(ChatMessageRequest $request)
    {
        $user = $request->user();
        $message = $request->validated()['message'];
        $chat_ai_id = $request->validated()['chat_ai_id'];
        $title = $request->validated()['title'] ?? null;

        // Récupérer ou créer une session ChatAi
        if ($chat_ai_id) {
            $chatAi = ChatAi::where('id_chat_ai', $chat_ai_id)
                ->where('patient_id', $user->id_user)
                ->firstOrFail();
        } else {
            $chatAi = ChatAi::create([
                'id_chat_ai' => Uuid::uuid4()->toString(),
                'patient_id' => $user->id_user,
                'title' => $title ?? 'Conversation du ' . now()->format('d/m/Y H:i'),
                'symptoms' => [],
                'conseil' => null,
            ]);
        }

        // Récupérer l'historique
        $history = Message::where('chat_ai_id', $chatAi->id_chat_ai)
            ->orderBy('created_at')
            ->pluck('content', 'statut')
            ->toArray();
        $history_text = implode("\n", array_merge(...array_map(fn($c, $s) => ["User: $c", "Bot: $s"], array_keys($history), array_values($history))));

        // Appeler l'API Gemini
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post(
            "https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=" . env('GEMINI_API_KEY'),
            [
                'contents' => [
                    ['parts' => [['text' => $this->systemPrompt]]],
                    ['parts' => [['text' => $history_text . "\nUser: $message"]]],
                ],
            ]
        );

        if ($response->status() === 200) {
            $data = $response->json();
            $bot_response = $data['candidates'][0]['content']['parts'][0]['text'] ?? 'Erreur lors de la réponse.';
        } else {
            \Log::error("Erreur Gemini: {$response->body()}");
            $bot_response = 'Erreur lors de la réponse du médecin.';
        }

        // Extraire les symptômes (simplifié, à ajuster selon la réponse de Gemini)
        $symptoms = str_contains(strtolower($bot_response), 'symptôme') ? explode(',', $bot_response) : [];

        // Sauvegarde transactionnelle
        return DB::transaction(function () use ($chatAi, $message, $bot_response, $user, $symptoms) {
            // Sauvegarder le message utilisateur
            $userMessage = Message::create([
                'id_message' => Uuid::uuid4()->toString(),
                'chat_ai_id' => $chatAi->id_chat_ai,
                'parent_message_id' => null,
                'content' => $message,
                'statut' => 'user',
                'user_id' => $user->id_user,
                'chat_id' => null,
            ]);

            // Sauvegarder la réponse du bot
            Message::create([
                'id_message' => Uuid::uuid4()->toString(),
                'chat_ai_id' => $chatAi->id_chat_ai,
                'parent_message_id' => $userMessage->id_message,
                'content' => $bot_response,
                'statut' => 'bot',
                'user_id' => $user->id_user,
                'chat_id' => null,
            ]);

            // Mettre à jour les symptômes
            $chatAi->update([
                'symptoms' => array_unique(array_merge(json_decode($chatAi->symptoms, true), $symptoms)),
                'conseil' => $bot_response,
            ]);

            return response()->json([
                'chat_ai_id' => $chatAi->id_chat_ai,
                'title' => $chatAi->title,
                'response' => $bot_response,
                'symptoms' => $symptoms,
            ]);
        });
    }

    public function getMessages($chatAiId): \Illuminate\Http\JsonResponse
    {
        $messages = Message::where('chat_ai_id', $chatAiId)
            ->orderBy('created_at')
            ->get(['id_message', 'parent_message_id', 'content', 'statut', 'created_at']);
        return response()->json($messages);
    }

    public function getConversations(Request $request): \Illuminate\Http\JsonResponse
    {
        $conversations = ChatAi::where('patient_id', $request->user()->id_user)
            ->get(['id_chat_ai', 'title', 'created_at']);
        return response()->json($conversations);
    }
}
