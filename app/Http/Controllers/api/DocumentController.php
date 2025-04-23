<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
        $documents = Document::all();
        return response()->json([
            'status' => 'success',
            'data' => $documents
        ], 200);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $validatedData = Validator:: make($request->all(),[
            'type'=>'required|string',
            'titre'=>'required|string',
            'file'=>'required|file|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'medecin_id' => 'nullable|exists:user,id_user',
        ]);

        if ($validatedData->fails()) {
            return response()->json([
                $validatedData->errors(),
                'message'=>'erreur de validation'
            ]);
        }

        try {

            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Authentication required'
                ], 401);
            }

            $medecinId = $request->medecin_id;
            if ($medecinId) {
                if ($user->role !== 'admin') {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Only administrators can specify a medecin_id'
                    ], 403);
                }


                $medecin = User::where('id_user', $medecinId)->where('role', 'medecin')->first();

                if (!$medecin) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'The specified medecin_id does not belong to a physician'
                    ], 422);
                }
            } else {

                if ($user->role !== 'medecin') {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'User is not a physician'
                    ], 403);
                }

                $medecinId = $user->id_user;
            }

            $file = $request->file('file');

            // Nettoyer le nom pour éviter les caractères spéciaux
            $nom = Str::slug($medecin->name);
            $type = Str::slug($request->type);
            $titre = Str::slug($request->titre);
            $date = date('Ymd');
            $extension = $file->getClientOriginalExtension();
            $fileName = "{$nom}_{$type}_{$titre}_{$date}.{$extension}";
            $chemin = $file->storeAs('documents', $fileName, 'public');

            $document = Document::create(
                [
                    'id_document'=> (string) Str::uuid(),
                    'titre'=>$request->titre,
                    'type'=>$request->type,
                    'file'=> $chemin,
                    'medecin_id'=>$medecinId,
                    'statut'=>0
                ]);
            return response()->json(
                [
                    'status' => 'success',
                    'data' => $document,
                    'message' => 'Document create successfully'
                ],201);
        }catch (\Exception $e){
            return response()->json(
                [
                    'status' => 'error',
                    'error' => $e->getMessage(),
                    'message' => 'Document not created'
                ],500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): \Illuminate\Http\JsonResponse
    {
        try {
            $document = Document::findOrFail($id);
            return response()->json([
                'status' => 'success',
                'data' => $document
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Document non trouvé'
            ], 404);
        }

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): \Illuminate\Http\JsonResponse
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Authentication required'
                ], 401);
            }

            $document = Document::findOrFail($id);

            if ($user->id_user !== $document->medecin_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Only the physician who uploaded this document can delete it'
                ], 403);
            }

            if ($document->file && Storage::disk('public')->exists($document->file)) {
                Storage::disk('public')->delete($document->file);
            }

            $document->delete();

            return response()->json([
                'status' => 'success',
                'data' => null,
                'message' => 'Document delete successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => $e->getMessage(),
                'message' => 'Document not deleted'
            ], 500);
        }
    }
}
