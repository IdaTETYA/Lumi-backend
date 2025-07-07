<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PredictionService
{
    private $pythonPath;
    private $scriptPath;

    public function __construct()
    {
        $this->pythonPath = env('PYTHON_PATH', 'C:\\ProgramData\\anaconda3\\python.exe');
        $this->scriptPath = storage_path('app/ML/model.py');
    }

    public function predireMaladie(array $symptoms, array $patientInfo)
    {
        Log::info('Début de predireMaladie', [
            'symptoms' => $symptoms,
            'patient_info' => $patientInfo
        ]);

        // Valider l'environnement
        $envValidation = $this->validerEnvironnement();
        if (!$envValidation['valid']) {
            Log::error('Environnement invalide', ['issues' => $envValidation['issues']]);
            throw new \Exception('Environnement invalide : ' . implode(', ', $envValidation['issues']));
        }

        // Préparer les données d'entrée pour le script Python
        $jsonInput = json_encode(['symptoms' => $symptoms, 'patient_info' => $patientInfo], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        $jsonInput = str_replace('"', '\\"', $jsonInput);

        $command = sprintf(
            '"%s" "%s" "%s"',
            escapeshellarg($this->pythonPath),
            escapeshellarg($this->scriptPath),
            $jsonInput
        );

        Log::debug('Exécution de la commande Python', ['command' => $command]);

        $output = [];
        $returnVar = 0;
        exec($command, $output, $returnVar);

        $outputStr = implode('', $output);
        Log::debug('Sortie brute Python', ['output' => $outputStr]);

        if ($returnVar !== 0) {
            Log::error('Erreur lors de l\'exécution du script Python', [
                'command' => $command,
                'output' => $outputStr,
                'return_var' => $returnVar
            ]);
            throw new \Exception('Erreur lors de l\'exécution du modèle de prédiction: ' . $outputStr);
        }

        if (empty($outputStr) || !($result = json_decode($outputStr, true))) {
            Log::error('Sortie Python invalide ou vide', ['output' => $outputStr]);
            throw new \Exception('Sortie Python invalide ou vide');
        }

        Log::info('Résultat de la prédiction', ['result' => $result]);

        if (!$result || !isset($result['success']) || !$result['success']) {
            Log::error('Échec de la prédiction', [
                'result' => $result,
                'output' => $outputStr
            ]);
            throw new \RuntimeException('Échec de la prédiction: ' . ($result['error'] ?? 'Erreur inconnue'));
        }

        return $result;
    }

    public function genererRapport(array $prediction, array $patientInfo): array
    {
        $confidence = $prediction['confidence'] ?? null;
        $niveauConfiance = $this->getNiveauConfiance($confidence);
        $matchingRate = $prediction['metadata']['matching_rate'] ?? 0;
        $fiabilite = $this->determinerFiabilite($confidence, $matchingRate);

        $pointsAttention = [];
        $recommandations = [];

        if ($confidence < 0.4) {
            $pointsAttention[] = 'Confiance faible du modèle';
            $recommandations[] = 'Diagnostic différentiel élargi recommandé';
        }
        if ($matchingRate < 80) {
            $pointsAttention[] = 'Plusieurs symptômes non reconnus par le modèle';
            $recommandations[] = 'Évaluer les symptômes non standard rapportés';
        }
        if ($confidence >= 0.8) {
            $recommandations[] = 'Prédiction très fiable, peut orienter le diagnostic';
        }

        return [
            'patient_nom' => $patientInfo['nom'] ?? 'Inconnu',
            'patient_age' => $patientInfo['age'] ?? null,
            'symptomes_rapportes' => $prediction['symptomes'] ?? [],
            'symptomes_reconnus' => $prediction['symptomes_reconnus'] ?? [],
            'symptomes_non_reconnus' => $prediction['symptomes_non_reconnus'] ?? [],
            'maladie_predite' => $prediction['maladie'] ?? 'Inconnue',
            'confiance' => $confidence,
            'niveau_confiance' => $niveauConfiance,
            'predictions_alternatives' => $prediction['top_predictions'] ?? [],
            'taux_correspondance' => $matchingRate,
            'priorite' => $this->determinerPriorite($confidence, $prediction['maladie'] ?? ''),
            'date_rapport' => Carbon::now()->format('Y-m-d H:i:s'),
            'type_rapport' => 'transmission_medecin',
            'statut' => 'en_attente_medecin',
            'analyse_qualite' => [
                'fiabilite' => $fiabilite,
                'points_attention' => $pointsAttention,
                'recommandations_medecin' => $recommandations
            ]
        ];
    }

    private function getNiveauConfiance(?float $confidence): string
    {
        if ($confidence === null) {
            return 'Inconnu';
        }
        if ($confidence > 0.85) {
            return 'Très élevé';
        }
        if ($confidence > 0.70) {
            return 'Élevé';
        }
        if ($confidence > 0.55) {
            return 'Moyen';
        }
        if ($confidence > 0.40) {
            return 'Faible';
        }
        return 'Très faible';
    }

    private function determinerFiabilite(?float $confidence, float $matchingRate): string
    {
        if ($confidence === null || $matchingRate < 50) {
            return 'faible';
        }
        if ($confidence > 0.85 && $matchingRate > 80) {
            return 'elevee';
        }
        if ($confidence > 0.55 && $matchingRate > 60) {
            return 'moyenne';
        }
        return 'faible';
    }

    private function determinerPriorite(?float $confidence, string $disease): string
    {
        $maladiesUrgentes = ['Malaria', 'Dengue', 'Typhoid', 'Cholera'];
        if ($confidence === null) {
            return 'a_evaluer';
        }
        if (in_array($disease, $maladiesUrgentes) && $confidence > 0.7) {
            return 'urgente';
        }
        if ($confidence > 0.55) {
            return 'moderee';
        }
        return 'a_evaluer';
    }

    public function validerEnvironnement(): array
    {
        $issues = [];

        // Vérifier l'exécutable Python
        $testCommand = sprintf('"%s" --version 2>&1', escapeshellarg($this->pythonPath));
        exec($testCommand, $output, $returnVar);
        if ($returnVar !== 0) {
            $issues[] = "Python non trouvé ou non fonctionnel au chemin: {$this->pythonPath}. Sortie: " . implode(', ', $output);
        }

        // Vérifier le script Python
        if (!file_exists($this->scriptPath)) {
            $issues[] = "Script Python non trouvé: {$this->scriptPath}";
        }

        // Vérifier les fichiers du modèle
        $modelFiles = [
            'C:\Users\USER\Documents\MachineLearning\random_forest_moderate.pkl',
            'C:\Users\USER\Documents\MachineLearning\label_encoder_moderate.pkl',
            'C:\Users\USER\Documents\MachineLearning\symptoms_list.pkl'
        ];

        foreach ($modelFiles as $file) {
            if (!file_exists($file)) {
                $issues[] = "Fichier modèle manquant: " . basename($file);
            }
        }

        return [
            'valid' => empty($issues),
            'issues' => $issues
        ];
    }
}
