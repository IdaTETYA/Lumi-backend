<?php

use App\Http\Controllers\api\AdministrateurController;
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
        Route::post('medecins/{id}/valider', [AdministrateurController::class, 'validerMedecin']);
        Route::post('medecins/{id}/refuser', [AdministrateurController::class, 'refuserMedecin']);
        Route::post('medecins/{id}/valider/annuler', [AdministrateurController::class, 'annulerValidation']);
        Route::get('medecins/rejetes', [AdministrateurController::class, 'medecinRejete']);
        Route::get('medecins/valide', [AdministrateurController::class, 'medecinValide']);
    });
});
