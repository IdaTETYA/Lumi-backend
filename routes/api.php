<?php

use App\Http\Controllers\api\AdministrateurController;
use App\Http\Controllers\api\DocumentController;
use App\Http\Controllers\api\MedecinController;
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

