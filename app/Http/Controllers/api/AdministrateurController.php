<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\IdRequest;
use App\Http\Requests\statusRequest;
use App\Models\Medecin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class AdministrateurController
{
    public function medecinEnAttente(): JsonResponse
    {
        $medecins = User::where('role', 'medecin')->where('statut_compte', 0)->get();
        return response()->json([
            'medecins' => $medecins,
            'data' => 'liste medecins en attente de validation'
        ], 200);
    }

    public function validerMedecin(string $id): JsonResponse
    {
        $medecin = User::findOrFail($id);
        if ($medecin->statut_compte === 1) {
            return response()->json(['message' => 'Le médecin est déjà validé'], 400);
        }

        $medecin->statut_compte = 1;
        $medecin->save();

        return response()->json($medecin, 200);
    }

    public function refuserMedecin( string $id,Request $request): JsonResponse
    {
        $medecin = User::findOrFail($id);

        if ($medecin->statut_compte !== 0) {
            return response()->json([
                'message' => 'Le médecin n\'est pas en attente de validation',
                'medecin' => $medecin,
            ], 400);
        }

        $validatedData = $request->validate([
            'motif_refus' => 'required|string|max:255',
        ]);

        $medecin->statut_compte = -1;
        $medecin->motif_refus = $validatedData['motif_refus'];
        $medecin->save();

        return response()->json([
            'message' => 'Compte rejeté avec succès',
            'medecin' => $medecin,
        ], 200);
    }

    public function medecinValide(): JsonResponse
    {
        $medecin = User::where('role','medecin')->where('statut_compte', 1)->get();
        return response()->json($medecin, 200);
    }

    public function medecinRejete(): JsonResponse
    {
        $medecins = User::where('role','medecin')->where('statut_compte', -1)->get();
        return response()->json($medecins, 200);
    }


    public function annulerValidation(string $id): JsonResponse
    {
        $medecin = User:: findorFail($id);

        if ($medecin->role === 'medecin' && $medecin->statut_compte === 1)
        {
            $medecin->statut_compte = 0;
            $medecin->motif_refus = null;
            $medecin->save();
            return response()->json([$medecin],200);
        }
        return response()->json(['message'=>'le medecin n\'est pas validé'],422);
    }


}

