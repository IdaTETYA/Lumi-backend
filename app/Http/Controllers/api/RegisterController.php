<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    public function registerPatient(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:user,email|max:255',
            'password' => 'required|string|min:8',
            'nom' => 'required|string|max:20',
            'prenom' => 'required|string|max:20',
            'numero_telephone' => 'required|string',
            'sexe' => 'required',
            'date_de_naissance' => 'required|date',
            'ville' => 'required|string|max:255',
            'quartier' => 'required|string|max:255',
            'as_antecedent_familiaux' => 'required|boolean',
            'accepte_conditions'=>'required|boolean',

        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        try {
            $user = User::create([
                'id_user' => (string) Str::uuid(),
                'nom' => $request->nom,
                'prenom' => $request->prenom,
                'numero_telephone' => $request->numero_telephone,
                'sexe' => $request->sexe,
                'date_de_naissance' => $request->date_de_naissance,
                'ville' => $request->ville,
                'quartier' => $request->quartier,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'patient',
                'status_compte' => 1,
                'accepte_conditions'=>$request->accepte_conditions,

            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Compte créé avec succès',
                'user' => $user,
                'token' => $token,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la création du compte',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function registerMedecin(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:50',
            'prenom' => 'required|string|max:50',
            'email' => 'required|email|unique:user,email',
            'date_de_naissance' => 'required|date',
            'sexe' => 'required|string',
            'numero_telephone' => 'required|string|max:10',
            'specialite' => 'required|string|max:50',
            'numero_onmc' => 'required|string|max:10',
            'lieu_de_travail' => 'required|string|max:100',
            'ville' => 'required|string|max:50',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            Log::error($validator->errors()->toArray());
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()->toArray(),
            ], 422);

        }

        try {
            $user = User::create([
                'id_user' => (string) Str::uuid(),
                'nom' => $request->nom,
                'prenom' => $request->prenom,
                'email' => $request->email,
                'date_de_naissance' => $request->date_de_naissance,
                'sexe' => $request->sexe,
                'numero_telephone' => $request->numero_telephone,
                'specialite' => $request->specialite,
                'numero_onmc' => $request->numero_onmc,
                'lieu_de_travail' => $request->lieu_de_travail,
                'ville' => $request->ville,
                'password' => Hash::make($request->password),
                'role' => 'medecin',
                'status_compte' => 0,
                'accepte_conditions'=>$request->accepte_conditions,

            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'medecin' => $user,
                'message' => 'Médecin enregistré avec succès',
                'token' => $token,
            ], 201);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'message' => 'Erreur lors de l\'enregistrement du médecin',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function verifierEmail(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        $emailExist = User::where('email', $request->email)->exists();

        return response()->json([
            'exists' => $emailExist,
            'message' => $emailExist ? 'Email existant' : 'Email disponible',
        ], 200);
    }
}
