<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChatMessageRequest;
use App\Models\ChatAI;
use App\Models\Message;
use App\Services\PredictionService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;

class ChatAIController extends Controller
{
    private string $systemPrompt = '
        Tu es Lumi, un assistant médical IA spécialisé dans la préconsultation au Cameroun. Tu dialogues en français et anglais, en t\'adaptant aux expressions locales camerounaises.

        ## TON RÔLE
        - Recueillir les symptômes du patient de manière naturelle et empathique
        - Normaliser les symptômes selon la liste prédéfinie
        - Maintenir une conversation fluide comme avec un vrai médecin
        - NE JAMAIS poser de diagnostic ou donner de conseils médicaux

        ## LISTE DES SYMPTÔMES AUTORISÉS (FORMAT ANGLAIS POUR DATASET)
        [abdominal_pain, abnormal_menstruation, acidity, acute_liver_failure, altered_sensorium, anxiety, back_pain, belly_pain, blackheads, bladder_discomfort, blister, blood_in_sputum, bloody_stool, blurred_and_distorted_vision, breathlessness, brittle_nails, bruising, burning_micturition, chest_pain, chills, cold_hands_and_feets, coma, congestion, constipation, continuous_feel_of_urine, continuous_sneezing, cough, cramps, dark_urine, dehydration, depression, diarrhoea, dischromic_patches, distention_of_abdomen, dizziness, drying_and_tingling_lips, enlarged_thyroid, excessive_hunger, extra_marital_contacts, family_history, fast_heart_rate, fatigue, fluid_overload, foul_smell_of_urine, headache, high_fever, hip_joint_pain, history_of_alcohol_consumption, increased_appetite, indigestion, inflammatory_nails, internal_itching, irregular_sugar_level, irritability, irritation_in_anus, itching, joint_pain, knee_pain, lack_of_concentration, lethargy, loss_of_appetite, loss_of_balance, loss_of_smell, malaise, mild_fever, mood_swings, movement_stiffness, mucoid_sputum, muscle_pain, muscle_wasting, muscle_weakness, nausea, neck_pain, nodal_skin_eruptions, obesity, pain_behind_the_eyes, pain_during_bowel_movements, pain_in_anal_region, painful_walking, palpitations, passage_of_gases, patches_in_throat, phlegm, polyuria, prominent_veins_on_calf, puffy_face_and_eyes, pus_filled_pimples, receiving_blood_transfusion, receiving_unsterile_injections, red_sore_around_nose, red_spots_over_body, redness_of_eyes, restlessness, runny_nose, rusty_sputum, scurring, shivering, silver_like_dusting, sinus_pressure, skin_peeling, skin_rash, slurred_speech, small_dents_in_nails, spinning_movements, spotting_urination, stiff_neck, stomach_bleeding, stomach_pain, sunken_eyes, sweating, swelled_lymph_nodes, swelling_joints, swelling_of_stomach, swollen_blood_vessels, swollen_extremeties, swollen_legs, throat_irritation, toxic_look_(typhos), ulcers_on_tongue, unsteadiness, visual_disturbances, vomiting, watering_from_eyes, weakness_in_limbs, weakness_of_one_body_side, weight_gain, weight_loss, yellow_crust_ooze, yellow_urine, yellowing_of_eyes, yellowish_skin]

        ## RÈGLES DE CONVERSATION BILINGUE
        - **Conversation** : Adapte-toi à la langue du patient (français ou anglais)
        - **Expressions camerounaises** : Comprend et accepte les expressions locales
        - **Retour JSON** : Les symptômes doivent TOUJOURS être en anglais (format dataset)
        - **Flexibilité** : Si le patient mélange français/anglais, reste naturel

        ## EXEMPLES DE DIALOGUE BILINGUE

        **En français :**
        Patient: "J\'ai mal à la tête et je me sens chaud"
        Réponse: "Je comprends que vous avez mal à la tête et de la fièvre. Depuis quand ressentez-vous cela ?"
        JSON: {"symptomes": ["headache", "high_fever"], ...}

        **En anglais :**
        Patient: "I have a headache and I feel hot"
        Réponse: "I understand you have a headache and fever. How long have you been feeling this way?"
        JSON: {"symptomes": ["headache", "high_fever"], ...}

        **Mélange français/anglais :**
        Patient: "J\'ai headache et je feel hot"
        Réponse: "D\'accord, vous avez mal à la tête et de la fièvre. Pouvez-vous me dire depuis quand ?"
        JSON: {"symptomes": ["headache", "high_fever"], ...}

        ### 1. ACCUEIL CHALEUREUX
        - Salue le patient avec empathie
        - Pose une question ouverte pour l\'inviter à parler
        - Utilise des expressions camerounaises appropriées

        ### 2. ÉCOUTE ACTIVE
        - Laisse le patient s\'exprimer librement
        - Pose des questions de clarification naturelles
        - Reformule pour confirmer ta compréhension
        - Évite les questions trop techniques

        ### 3. QUESTIONS DE SUIVI BILINGUES
        **En français :**
        - "Depuis quand ressentez-vous cela ?"
        - "Pouvez-vous me décrire la douleur ?"
        - "Y a-t-il autre chose qui vous préoccupe ?"
        - "Comment vous sentez-vous généralement ?"

        **En anglais :**
        - "How long have you been feeling this way?"
        - "Can you describe the pain?"
        - "Is there anything else bothering you?"
        - "How do you feel generally?"

        ### 4. NORMALISATION DES SYMPTÔMES
        Expressions camerounaises courantes :
        - "Je me sens chaud" → fièvre élevée
        - "Mon corps me fait mal" → douleurs musculaires
        - "J\'ai le ventre qui tourne" → douleurs abdominales
        - "Ma tête me tape" → maux de tête
        - "Je suis fatigué-fatigué" → fatigue
        - "J\'ai froid-froid" → frissons
        - "Ça me gratte" → démangeaisons

        ### 5. GESTION DES RÉPONSES
        - Si symptômes clairs : Confirme et demande s\'il y a autre chose
        - Si symptômes ambigus : Demande précision avec bienveillance
        - Si hors sujet : Recentre délicatement sur la santé
        - Si "non/rien" : Termine la collecte

        ## FORMAT DE RÉPONSE JSON
        {
          "message": "Votre réponse empathique et naturelle",
          "symptomes": ["symptômes détectés dans ce message"],
          "symptomes_accumules": ["tous les symptômes collectés"],
          "etape": "demander_symptomes|confirmer_symptomes|terminer_collecte",
          "questions_complementaires": ["questions suggérées si nécessaire"]
        }

        ## EXEMPLES DE DIALOGUE

        **Début de conversation :**
        "Bonjour ! Je suis MediBot, votre assistant médical. Comment vous sentez-vous aujourd\'hui ? N\'hésitez pas à me dire ce qui vous préoccupe."

        **Pendant la collecte :**
        "Je comprends que vous avez des maux de tête. Est-ce que c\'est plutôt des douleurs qui tapent ou des douleurs continues ? Et depuis quand ça a commencé ?"

        **Confirmation :**
        "D\'accord, donc vous avez de la fièvre, des maux de tête et des douleurs au ventre. Y a-t-il autre chose qui vous gêne ou vous fait mal ?"

        **Fin de collecte :**
        "Merci pour ces informations. Je vais maintenant transmettre vos symptômes à un médecin pour une évaluation appropriée."

        ## CONTRAINTES IMPORTANTES
        - Reste dans ton rôle d\'assistant médical
        - Ne pose jamais de diagnostic
        - Reste empathique et professionnel
        - Utilise un langage simple et accessible
        - Respecte la culture camerounaise
        - Garde la conversation centrée sur les symptômes

        ---

        **CONTEXTE ACTUEL :**
        Message utilisateur : "{{message}}"
        Historique : {{historique}}
        Symptômes déjà collectés : {{symptomes_accumules}}

        Réponds UNIQUEMENT avec un JSON brut, sans balises markdown.';

    public function __construct(private PredictionService $predictionService)
    {
    }

    private function cleanGeminiResponse(string $response): string
    {
        $cleaned = preg_replace('/^```json\n([\s\S]*)\n```$/', '$1', trim($response));
        $cleaned = preg_replace('/^[^{]*(\{.*})[^}]*$/', '$1', $cleaned);
        return trim($cleaned);
    }

    public function chat(ChatMessageRequest $request)
    {
        Log::info('=== DEBUT CHAT ===', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'headers' => $request->headers->all(),
            'user_id' => $request->user()?->id_user
        ]);

        $transmissionId = null;

        try {
            Log::info('Début de chat', ['user_id' => $request->user()?->id_user, 'input' => $request->validated()]);

            $user = $request->user();
            if (!$user) {
                Log::warning('Utilisateur non authentifié dans chat');
                return response()->json(['error' => 'Utilisateur non authentifié'], 401);
            }

            $message = $request->validated('message');
            $chat_ai_id = $request->validated('chat_ai_id');

            // Récupérer ou créer une session ChatAI
            if ($chat_ai_id) {
                $chatAi = ChatAI::where('id_chat_ai', $chat_ai_id)
                    ->where('patient_id', $user->id_user)
                    ->firstOrFail();
            } else {
                $chatAi = ChatAI::create([
                    'id_chat_ai' => Uuid::uuid4()->toString(),
                    'patient_id' => $user->id_user,
                    'title' => 'Consultation du ' . now()->format('d/m/Y H:i'),
                    'symptoms' => [],
                    'conseil' => null,
                ]);
            }

            // Récupérer l'historique
            $history = Message::where('chat_ai_id', $chatAi->id_chat_ai)
                ->orderBy('created_at')
                ->pluck('content', 'role')
                ->toArray();
            $history_text = implode("\n", array_map(fn($s, $c) => "User: $c\nBot: $s", array_keys($history), array_values($history)));

            // Préparer les symptômes accumulés pour le prompt
            $symptomes_accumules = is_array($chatAi->symptoms) ? implode(', ', $chatAi->symptoms) : '';

            // Remplacer les placeholders dans le prompt
            $prompt = str_replace(
                ['{{message}}', '{{historique}}', '{{symptomes_accumules}}'],
                [$message, $history_text, $symptomes_accumules],
                $this->systemPrompt
            );

            // Appeler l'API Gemini
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post(
                "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . env('GEMINI_API_KEY'),
                [
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [['text' => $prompt]],
                        ],
                    ],
                ]
            );

            if ($response->status() === 200) {
                $data = $response->json();
                $bot_response = $data['candidates'][0]['content']['parts'][0]['text'] ?? '{}';

                // Nettoyer la réponse
                Log::debug('Réponse brute Gemini', ['response' => $bot_response]);
                $bot_response = $this->cleanGeminiResponse($bot_response);
                Log::debug('Réponse nettoyée', ['response' => $bot_response]);

                // Parser la réponse JSON
                $bot_data = json_decode($bot_response, true, 512, JSON_THROW_ON_ERROR);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    Log::error('Échec du parsing JSON', ['response' => $bot_response, 'json_error' => json_last_error_msg()]);
                    $bot_data = [
                        'message' => 'Erreur lors de la réponse du médecin. Veuillez réessayer.',
                        'symptomes' => [],
                        'symptomes_accumules' => $chatAi->symptoms,
                        'etape' => 'demander_symptomes'
                    ];
                }

                // Si la collecte est terminée, lancer la prédiction
                if ($bot_data['etape'] === 'terminer_collecte') {
                    $patientInfo = [
                        'nom' => $user->nom ?? 'Patient Anonyme',
                        'age' => $user->date_de_naissance ? Carbon::parse($user->date_de_naissance)->age : null,
                        'telephone' => $user->numero_telephone ?? null,
                        'email' => $user->email ?? null
                    ];

                    try {
                        // Lancer la prédiction
                        $prediction = $this->predictionService->predireMaladie($bot_data['symptomes_accumules'], $patientInfo);

                        // Générer le rapport
                        $rapport = $this->predictionService->genererRapport($prediction, $patientInfo);

                        // Sauvegarder dans la base de données
                        $transmissionId = DB::table('transmissions_medecin')->insertGetId([
                            'patient_id' => $user->id_user,
                            'chat_ai_id' => $chatAi->id_chat_ai,
                            'symptomes' => json_encode($bot_data['symptomes_accumules'], JSON_THROW_ON_ERROR),
                            'maladie_predite' => $prediction['maladie'],
                            'confiance' => $prediction['confidence'],
                            'priorite' => $rapport['priorite'],
                            'rapport_complet' => json_encode($rapport, JSON_THROW_ON_ERROR),
                            'statut' => 'en_attente',
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);

                        // Mettre à jour le statut de la conversation
                        $chatAi->update([
                            'symptoms' => $bot_data['symptomes_accumules'],
                            'conseil' => 'Transmission envoyée au médecin.',
                            'status' => 'transmis'
                        ]);

                        // Ajouter un message final
                        $bot_data['message'] = 'Merci, vos symptômes ont été transmis au médecin pour évaluation.';
                    } catch (Exception $e) {
                        Log::error('Erreur lors de la prédiction: ' . $e->getMessage());
                        $bot_data['message'] = 'Erreur lors de la transmission au médecin. Veuillez réessayer.';
                    }
                }

                // Sauvegarde transactionnelle
                return DB::transaction(function () use ($chatAi, $message, $bot_data, $user, $transmissionId) {
                    // Sauvegarder le message utilisateur
                    $userMessage = Message::create([
                        'id_message' => Uuid::uuid4()->toString(),
                        'chat_ai_id' => $chatAi->id_chat_ai,
                        'parent_message_id' => null,
                        'content' => $message,
                        'role' => 'user',
                        'user_id' => $user->id_user,
                    ]);

                    // Sauvegarder la réponse du bot
                    Message::create([
                        'id_message' => Uuid::uuid4()->toString(),
                        'chat_ai_id' => $chatAi->id_chat_ai,
                        'parent_message_id' => $userMessage->id_message,
                        'content' => $bot_data['message'],
                        'role' => 'bot',
                        'user_id' => $user->id_user,
                    ]);

                    // Mettre à jour les symptômes
                    $existingSymptoms = is_array($chatAi->symptoms) ? $chatAi->symptoms : [];
                    $newSymptoms = array_unique(array_merge($existingSymptoms, $bot_data['symptomes']));
                    $chatAi->update([
                        'symptoms' => $newSymptoms,
                    ]);

                    Log::info('Message envoyé et réponse enregistrée', ['chat_ai_id' => $chatAi->id_chat_ai]);

                    return response()->json([
                        'chat_ai_id' => $chatAi->id_chat_ai,
                        'title' => $chatAi->title,
                        'response' => $bot_data['message'],
                        'symptoms' => $newSymptoms,
                        'etape' => $bot_data['etape'],
                        'transmission_id' => $transmissionId,
                    ], 201);
                });
            }

            Log::error("Erreur Gemini: {$response->body()}", ['status' => $response->status()]);
            $bot_data = [
                'message' => 'Erreur lors de la réponse du médecin.',
                'symptomes' => [],
                'symptomes_accumules' => $chatAi->symptoms,
                'etape' => 'demander_symptomes'
            ];

            // Sauvegarde transactionnelle pour les cas d'erreur
            return DB::transaction(function () use ($chatAi, $message, $bot_data, $user, $transmissionId) {
                $userMessage = Message::create([
                    'id_message' => Uuid::uuid4()->toString(),
                    'chat_ai_id' => $chatAi->id_chat_ai,
                    'parent_message_id' => null,
                    'content' => $message,
                    'role' => 'user',
                    'user_id' => $user->id_user,
                ]);

                Message::create([
                    'id_message' => Uuid::uuid4()->toString(),
                    'chat_ai_id' => $chatAi->id_chat_ai,
                    'parent_message_id' => $userMessage->id_message,
                    'content' => $bot_data['message'],
                    'role' => 'bot',
                    'user_id' => $user->id_user,
                ]);

                return response()->json([
                    'chat_ai_id' => $chatAi->id_chat_ai,
                    'title' => $chatAi->title,
                    'response' => $bot_data['message'],
                    'symptoms' => $chatAi->symptoms,
                    'etape' => $bot_data['etape'],
                    'transmission_id' => $transmissionId,
                ], 201);
            });
        } catch (Exception $e) {
            Log::error('Erreur dans chat: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'Erreur serveur: ' . $e->getMessage()], 500);
        }
    }

    public function getMessages($chatAiId): JsonResponse
    {
        try {
            Log::info('Début de getMessages', ['chat_ai_id' => $chatAiId]);

            $messages = Message::where('chat_ai_id', $chatAiId)
                ->orderBy('created_at')
                ->get(['id_message', 'parent_message_id', 'content', 'role', 'created_at']);

            Log::info('Messages récupérés', ['count' => $messages->count()]);

            return response()->json($messages);
        } catch (Exception $e) {
            Log::error('Erreur dans getMessages: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'Erreur serveur: ' . $e->getMessage()], 500);
        }
    }

    public function getConversations(Request $request): JsonResponse
    {
        try {
            Log::info('Début de getConversations', ['user_id' => $request->user()?->id_user]);

            $user = $request->user();
            if (!$user) {
                Log::warning('Utilisateur non authentifié dans getConversations');
                return response()->json(['error' => 'Utilisateur non authentifié'], 401);
            }

            Log::info('Utilisateur authentifié dans getConversations', ['user_id' => $user->id_user]);

            $conversations = ChatAI::where('patient_id', $user->id_user)
                ->select('id_chat_ai', 'title', 'created_at')
                ->orderBy('created_at', 'desc')
                ->get();

            Log::info('Conversations récupérées', ['count' => $conversations->count()]);

            return response()->json($conversations);
        } catch (Exception $e) {
            Log::error('Erreur dans getConversations: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'Erreur serveur: ' . $e->getMessage()], 500);
        }
    }
}
