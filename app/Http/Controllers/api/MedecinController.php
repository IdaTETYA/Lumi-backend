<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\IdRequest;
use App\Models\Document;
use App\Models\Medecin;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MedecinController extends Controller
{

//    public function store(Request $request): \Illuminate\Http\JsonResponse
//    {
//        $validateData= Validator::make($request->all(),
//            [
//                'nom' => 'required|string|max:50',
//                'prenom' => 'required|string|max:50',
//                'email' => 'required|email|unique:doctors,email,',
//                'date_de_naissance' => 'required',
//                'sexe' => 'required|string|in:masculin,feminin',
//                'numero_telephone' => 'required|max:10',
//                'specilalite' => 'required|max:50',
//                'numero_onmc'=> 'required|max:10',
//                'lieu_de_travail'=> 'required|string|max:100',
//                'ville'=> 'required|string|max:50',
//                'password' => 'required|string|min:8|confirmed',
//                'password_confirmation' => 'required',
//
//            ]);
//
//        if ($validateData->fails()) {
//            return response()->json($validateData->errors());
//        }
//        try {
//            $user_id=(string) Str::uuid();
//            $role='medecin';
//            $status=0;
//            $medecin = User::create(array_merge($request->all(), ['id_user' => $user_id],['role'=>$role],['status_compte'=>$status]));
//
//            return response()->json([
//                'medecin' => $medecin,
//                'message' => 'Médecin enregistré avec succès',
//                'status'=>  200
//            ]);
//        }catch (\Exception $exception){
//            return response()->json(['error'=>$exception->getMessage()]);
//        }
//
//    }

    /**
     * Display a listing of the resource.
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
        $medecins = User::where('role','medecin')->get();
        return response()->json($medecins,201);
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id): \Illuminate\Http\JsonResponse
    {
        $medecin=User::findorFail($id);
        return response()->json([
            'medecin' => $medecin,
            'status'=>'success'
        ],202);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $medecin=User::findorFail($id);

        $validateData= Validator::make($request->all(),
            [
                'nom' => 'required|string|max:50',
                'prenom' => 'required|string|max:50',
                'email' => 'required|email|unique:doctors,email,' . $medecin->id,
                'date_de_naissance' => 'required',
                'sexe' => 'required|string|in:masculin,feminin',
                'numero_telephone' => 'required|max:10',
                'specilalite' => 'required|max:50',
                'numero_onmc'=> 'required|max:10',
                'lieu_de_travail'=> 'required|string|max:100',
                'ville'=> 'required|string|max:50',
                'password' => 'required|string|min:8|confirmed',
                'confirm_password' => 'nullable|string|required_with:password|same:password'
            ]);

        if ($validateData->fails()) {
            return response()->json($validateData->errors(),400);
        }

        try {
            $medecin->update($request->all());
            return response()->json([
                'medecin' => $medecin,
                'message'=> 'medecin modifié avec succès',
            ],203);
        }catch (\Exception $exception)
        {
            return response()->json(['error'=>$exception->getMessage()],500);
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $medecin=User::findorFail($id);
        $medecin->delete();
        return response()->json
        ([
            'message'=> 'medecin supprimé  avec succès',
            'data'=> $medecin,


        ],204);
    }

    //liste des documents d'un medecin
    public function showDocument(string $id): JsonResponse
    {
        try {
            $documents = Document::where('medecin_id', $id)->get();

            $total = $documents->count();
            $verified = $documents->where('statut', 1)->count();

            return response()->json([
                'documents' => $documents,
                'total' => $total,
                'verified' => $verified,
                'status' => 'success'
            ], 200);
        } catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 500);
        }
    }


}
