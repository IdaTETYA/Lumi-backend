<?php

use App\Http\Controllers\api\AdministrateurController;
use App\Http\Controllers\api\ChatAIController;
use App\Http\Controllers\api\ConsultationController;
use App\Http\Controllers\api\DocumentController;
use App\Http\Controllers\api\MedecinController;
use App\Http\Controllers\api\TypeConsultationController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\LoginController;
use App\Http\Controllers\api\RegisterController;


Route::post('/login', [LoginController::class, 'login']);
Route::post('/register/patient', [RegisterController::class, 'registerPatient']);
Route::post('/register/medecin', [RegisterController::class, 'registerMedecin']);
Route::post('/register/verifierEmail', [RegisterController::class, 'verifierEmail']);


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout']);

    Route::prefix('consultations')->group(function () {
        Route::post('/', [ConsultationController::class, 'store']);
        Route::get('/patient/{patientId}', [ConsultationController::class, 'getConsultationsPatient']);
        Route::get('/patient/{patientId}/recentes', [ConsultationController::class, 'getConsultationsRecentes']);
        Route::get('/{id}', [ConsultationController::class, 'show']);
        Route::put('/{id}', [ConsultationController::class, 'update']);
        Route::patch('/{id}/terminer', [ConsultationController::class, 'terminer']);
        Route::delete('/{id}', [ConsultationController::class, 'destroy']);
    });

    // Routes types de consultation
    Route::get('/types-consultation', [TypeConsultationController::class, 'index']);

    // CORRECTION 1: Routes médecins publiques (pour l'affichage dans l'app)
    // Ces routes doivent correspondre exactement à ce qui est appelé dans le Flutter
    Route::get('/medecin', [MedecinController::class, 'indexMedecin']); // Route principale
    Route::get('/medecins/search', [MedecinController::class, 'search']); // Recherche
    Route::get('/medecins/recommandes', [MedecinController::class, 'getRecommandes']); // Recommandés
    Route::get('/medecins/{id}', [MedecinController::class, 'showMedecin']); // Détail d'un médecin
    Route::get('/medecins/{id}/creneaux', [MedecinController::class, 'getCreneauxDisponibles']); // Créneaux

    // CORRECTION 2: Route alternative pour compatibilité
    Route::get('/medecins', [MedecinController::class, 'indexMedecin']); // Route alternative


    Route::middleware(AdminMiddleware::class)->group(function () {
        route::post('medecins', [AdministrateurController::class, 'store']);
        Route::get('medecins/en-attente', [AdministrateurController::class, 'medecinEnAttente']);
        Route::put('medecins/{id}/valider', [AdministrateurController::class, 'validerMedecin']);
        Route::put('medecins/{id}/refuser', [AdministrateurController::class, 'refuserMedecin']);
        Route::post('medecins/{id}/annuler', [AdministrateurController::class, 'annulerValidation']);
        Route::get('medecins/rejetes', [AdministrateurController::class, 'medecinRejete']);
        Route::get('medecins/valide', [AdministrateurController::class, 'medecinValide']);

        Route::get('medecins', [MedecinController::class, 'index']);
        Route::get('medecins/{id}', [MedecinController::class, 'show']);
        Route::post('medecins/{id}/document/create', [DocumentController::class, 'store']);
        Route::post('medecins/{id}/document/{id_document}/update', [DocumentController::class, 'update']);
        Route::delete('medecins/{id}/document/{id_document}/delete', [DocumentController::class, 'destroy']);
        Route::get('medecins/{id}/documents', [MedecinController::class, 'showDocument']);
        Route::get('medecins/{id}/document/{id_document}', [DocumentController::class, 'show']);
        Route::get('medecins/{id}/document/{id_document}/download', [MedecinController::class, 'downloadDocument']);


        Route::get('documents', [DocumentController::class, 'index']);
        Route::get('documents/{id}', [DocumentController::class, 'show']);
        Route::get('documents/{id}/download', [DocumentController::class, 'download']);
        Route::put('documents/{id}/valider', [DocumentController::class, 'validerDocument']);


    });

});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/chat', [ChatAIController::class, 'chat']);
    Route::get('/chat/{chatAiId}/messages', [ChatAIController::class, 'getMessages']);
    Route::get('/conversations', [ChatAIController::class, 'getConversations']);
});

Route::get('/chat/test/{chat_ai}', [ChatAIController::class, 'getHistoryMessage']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/debug/medecins', function() {
        $medecins = \App\Models\User::where('role', 'medecin')->get();
        return response()->json([
            'count' => $medecins->count(),
            'medecins' => $medecins,
            'first_medecin' => $medecins->first(),
        ]);
    });

    Route::get('/debug/database', function() {
        try {
            $tables = \Illuminate\Support\Facades\DB::select('SHOW TABLES');
            $userCount = \App\Models\User::count();
            $medecinCount = \App\Models\User::where('role', 'medecin')->count();

            return response()->json([
                'database_connected' => true,
                'tables' => $tables,
                'total_users' => $userCount,
                'total_medecins' => $medecinCount,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'database_connected' => false,
                'error' => $e->getMessage()
            ]);
        }
    });
});
