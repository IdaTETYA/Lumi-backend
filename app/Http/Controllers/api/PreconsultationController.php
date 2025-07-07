<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Services\PredictionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PreconsultationController extends Controller
{
    private PredictionService $predictionService;

    public function __construct(PredictionService $predictionService)
    {
        $this->predictionService = $predictionService;
    }

    /**
     * Collecter les symptômes et générer un rapport pour le médecin
     */
    public function collectedSymptoms(Request $request): \Illuminate\Http\JsonResponse
    {
        // Validation des données
        $validator = Validator::make($request->all(), [
            'symptoms' => 'required|array|min:1',
            'symptoms.*' => 'string',
            'patient' => 'required|array',
            'patient.nom' => 'required|string',
            'patient.age' => 'nullable|integer|min:0|max:120',
            'patient.telephone' => 'nullable|string',
            'patient.email' => 'nullable|email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $symptoms = $request->input('symptoms');
            $patientInfo = $request->input('patient');

            // 1. Analyser les symptômes avec le modèle ML
            $prediction = $this->predictionService->predireMaladie($symptoms, $patientInfo);

            // 2. Générer le rapport pour le médecin
            $rapport = $this->predictionService->genererRapport($prediction, $patientInfo);

            // 3. Sauvegarder en base de données
            $transmissionId = DB::table('transmissions_medecin')->insertGetId([
                'patient_nom' => $patientInfo['nom'],
                'patient_age' => $patientInfo['age'] ?? null,
                'patient_telephone' => $patientInfo['telephone'] ?? null,
                'patient_email' => $patientInfo['email'] ?? null,
                'symptomes' => json_encode($symptoms, JSON_THROW_ON_ERROR),
                'maladie_predite' => $prediction['maladie'],
                'confiance' => $prediction['confidence'],
                'priorite' => $rapport['priorite'],
                'rapport_complet' => json_encode($rapport, JSON_THROW_ON_ERROR),
                'statut' => 'en_attente',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // 4. Retourner confirmation au patient (sans recommandations)
            return response()->json([
                'success' => true,
                'message' => 'Vos symptômes ont été transmis au médecin.',
                'transmission_id' => $transmissionId,
                'statut' => 'transmis',
                'priorite' => $rapport['priorite'],
                'message_priorite' => $this->getMessagePriorite($rapport['priorite'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de la transmission: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer un rapport pour le médecin
     */
    public function rapportMedicine($transmissionId): \Illuminate\Http\JsonResponse
    {
        try {
            $transmission = DB::table('transmissions_medecin')->where('id', $transmissionId)->first();

            if (!$transmission) {
                return response()->json([
                    'success' => false,
                    'error' => 'Transmission non trouvée'
                ], 404);
            }

            $rapport = json_decode($transmission->rapport_complet, true);

            return response()->json([
                'success' => true,
                'transmission_id' => $transmissionId,
                'patient' => [
                    'nom' => $transmission->patient_nom,
                    'age' => $transmission->patient_age,
                    'telephone' => $transmission->patient_telephone,
                    'email' => $transmission->patient_email
                ],
                'symptomes' => [
                    'rapportes' => json_decode($transmission->symptomes, true),
                    'reconnus' => $rapport['symptomes_reconnus'] ?? [],
                    'non_reconnus' => $rapport['symptomes_non_reconnus'] ?? [],
                    'taux_correspondance' => $rapport['taux_correspondance'] ?? 0
                ],
                'prediction_ml' => [
                    'maladie_principale' => $transmission->maladie_predite,
                    'confiance' => $transmission->confiance,
                    'niveau_confiance' => $rapport['niveau_confiance'] ?? null,
                    'predictions_alternatives' => $rapport['predictions_alternatives'] ?? []
                ],
                'analyse_ia' => [
                    'qualite_prediction' => $rapport['analyse_qualite'] ?? [],
                    'priorite' => $transmission->priorite,
                    'recommandations_systeme' => $this->getRecommandationsSysteme($transmission->priorite, $rapport)
                ],
                'statut' => $transmission->statut,
                'date_transmission' => $transmission->created_at,
                'rapport_complet' => $rapport
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de la récupération: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lister toutes les transmissions pour l'interface médecin
     */
    public function listerTransmissions(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $query = DB::table('transmissions_medecin')
                ->select('id', 'patient_nom', 'patient_age', 'maladie_predite', 'confiance', 'priorite', 'statut', 'created_at')
                ->orderBy('priorite', 'desc') // Urgentes en premier
                ->orderBy('created_at', 'desc');

            // Filtrer par statut si spécifié
            if ($request->has('statut')) {
                $query->where('statut', $request->get('statut'));
            }

            // Filtrer par priorité si spécifié
            if ($request->has('priorite')) {
                $query->where('priorite', $request->get('priorite'));
            }

            $transmissions = $query->paginate(20);

            return response()->json([
                'success' => true,
                'transmissions' => $transmissions
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de la récupération: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Marquer une transmission comme traitée par le médecin
     */
    public function marquerTraite($transmissionId): \Illuminate\Http\JsonResponse
    {
        try {
            $updated = DB::table('transmissions_medecin')
                ->where('id', $transmissionId)
                ->update([
                    'statut' => 'traitee',
                    'date_traitement' => now(),
                    'updated_at' => now()
                ]);

            if (!$updated) {
                return response()->json([
                    'success' => false,
                    'error' => 'Transmission non trouvée'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Transmission marquée comme traitée'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de la mise à jour: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getRecommandationsSysteme($priorite, $rapport): array
    {
        $recommandations = [];

        switch ($priorite) {
            case 'urgente':
                $recommandations[] = 'Consultation immédiate recommandée';
                $recommandations[] = 'Surveiller les signes vitaux';
                break;

            case 'elevee':
                $recommandations[] = 'Consultation dans les plus brefs délais';
                $recommandations[] = 'Examens complémentaires à prévoir';
                break;

            case 'normale':
                $recommandations[] = 'Consultation de routine appropriée';
                break;

            case 'a_evaluer':
                $recommandations[] = 'Évaluation clinique approfondie recommandée';
                $recommandations[] = 'Diagnostic différentiel à considérer';
                break;
        }

        // Recommandations basées sur l'analyse de qualité
        if (isset($rapport['analyse_qualite']['recommandations_medecin'])) {
            $recommandations = array_merge($recommandations, $rapport['analyse_qualite']['recommandations_medecin']);
        }

        return array_unique($recommandations);
    }

    private function getMessagePriorite($priorite): string
    {
        $messages = [
            'urgente' => 'Votre cas nécessite une attention médicale immédiate.',
            'elevee' => 'Votre transmission sera traitée en priorité.',
            'normale' => 'Votre transmission sera traitée dans les meilleurs délais.',
            'a_evaluer' => 'Votre cas nécessite une évaluation médicale approfondie.'
        ];

        return $messages[$priorite] ?? 'Votre transmission a été enregistrée.';
    }

    /**
     * Test de la connexion Python
     */
    public function testPython(): \Illuminate\Http\JsonResponse
    {
        try {
            $testSymptoms = ['fever', 'cough'];
            $testPatient = ['nom' => 'Test Patient', 'age' => 30];

            $result = $this->predictionService->predireMaladie($testSymptoms, $testPatient);

            return response()->json([
                'success' => true,
                'message' => 'Python fonctionne correctement',
                'test_result' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Erreur Python: ' . $e->getMessage()
            ], 500);
        }
    }
}
