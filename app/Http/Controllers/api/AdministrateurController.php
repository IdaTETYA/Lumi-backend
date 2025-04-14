<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Medecin;
use Illuminate\Http\Request;

class AdministrateurController extends Controller
{
    public  function medecinEnAttente(): \Illuminate\Http\JsonResponse
    {
        $medecin = Medecin::where('statut_compte', 0)->get();
        return response()->json($medecin,200);

    }

    public function validerMedecin($id)
    {
        $medecin = Medecin::findorFail($id);

        if ( $medecin->statut_compte === 1)
        {
            return response()->json('le medecin est deja valide',400);
        }
        $medecin->statut_compte = 1;
        $medecin->save();

        return response()->json(
            [
                'message'=>'le medecin validé avec succès',
                'medecin'=>$medecin,
            ],201);
    }


    public function refuserMedecin(Request $request,$id): \Illuminate\Http\JsonResponse
    {
        $medecin = Medecin::findorFail($id);

        $validatedData = $request->validate([
            'motif_refus' => 'required|string|max:255',
        ]);

        if ( $medecin->statut_compte === 0){
            $medecin->statut_compte = -1;
            $medecin->motif_refus = $validatedData['motif_refus'];
            $medecin->save();

            return response()->json(
                [
                    'medecin'=>$medecin,
                    'message'=> 'compte rejeté avec success',

               ], 203);
        }

        return response()->json(
            [
                'message'=> 'le medecin est deja valide',
                'medecin'=>$medecin,
            ]
        );

    }


    public function medecinRejete(){
        $medecin = Medecin:: where('statut_compte', -1)->get();
        return response()->json($medecin,200);


    }
}
