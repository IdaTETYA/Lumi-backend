<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    /**
     * Register a new user and return an API token.
     */
    public function register(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:user,email|max:255',
            'password' => 'required|string|min:8',
            'confirm_password' => 'nullable|string|required_with:password|same:password',
            'nom' => 'required|string|max:20',
            'prenom' => 'required|string|max:20',
            'numero_telephone' => 'required|string|max:9',
            'sexe' => 'required|in:masculin,feminin',
            'date_de_naissance' => 'required|date',
            'ville' => 'required|string|max:255',
            'pays' => 'required|string|max:255',
            'quartier' => 'required|string|max:255',
            'stade_de_grossesse' => 'required|integer|max:2',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()->all(),
            ], 400);
        }

        try {
            $user = User::create([
                'id_user'=>(string) Str::uuid(),
                'nom' => $request->nom,
                'prenom' => $request->prenom,
                'numero_telephone' => $request->telephone,
                'sexe' => $request->sexe,
                'date_de_naissance' => $request->date_de_naissance,
                'ville' => $request->ville,
                'pays' => $request->pays,
                'quartier' => $request->quartier,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'stade_de_grossese'=>$request->stade_de_grossesse,
                'role' => 'patient',
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
}
