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
use Illuminate\Support\Facades\Log;

class MedecinController extends Controller
{
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
     * CORRECTION 1: Récupérer tous les médecins validés avec mapping correct
     */
    public function indexMedecin(): JsonResponse
    {
        try {
            // Récupérer tous les médecins validés
            $medecins = User::where('role', 'medecin')
                ->where('statut_compte', 1) // CORRECTION: Nom de colonne corrigé
                ->select([
                    'id_user',
                    'nom',
                    'prenom',
                    'email',
                    'ville',
                    'quartier', // Ajouté pour Flutter
                    'specialite',
                    'lieu_de_travail',
                    'numero_telephone',
                    'latitude_lieu_de_travail as latitude',
                    'longitude_lieu_de_travail as longitude'
                ])
                ->get()
                ->map(function($medecin) {
                    // CORRECTION 2: Mapping pour correspondre au modèle Flutter
                    return [
                        'id_user' => $medecin->id_user,
                        'nom' => $medecin->nom,
                        'prenom' => $medecin->prenom,
                        'email' => $medecin->email,
                        'ville' => $medecin->ville,
                        'quartier' => $medecin->quartier ?? '',
                        'specialite' => $medecin->specialite,
                        'lieu_de_travail' => $medecin->lieu_de_travail,
                        'numero_telephone' => $medecin->numero_telephone,
                        'latitude' => $medecin->latitude,
                        'longitude' => $medecin->longitude,
                        // Propriétés par défaut pour l'affichage Flutter
                        'note' => 4.8,
                        'nombre_consultations' => rand(50, 300),
                        'disponible' => true,
                        'prochain_creneau' => null
                    ];
                });

            Log::info('Médecins récupérés:', ['count' => $medecins->count()]);

            return response()->json([
                'data' => $medecins,
                'message' => 'Médecins récupérés avec succès'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erreur récupération médecins:', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Erreur lors de la récupération des médecins',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * CORRECTION 3: Rechercher des médecins avec mapping correct
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q', '');

        if (empty($query) || strlen($query) < 2) {
            return response()->json([
                'data' => [],
                'message' => 'Terme de recherche trop court'
            ], 200);
        }

        try {
            $medecins = User::where('role', 'medecin')
                ->where('statut_compte', 1) // CORRECTION: Nom de colonne corrigé
                ->where(function($q) use ($query) {
                    $q->where('nom', 'LIKE', "%{$query}%")
                        ->orWhere('prenom', 'LIKE', "%{$query}%")
                        ->orWhere('specialite', 'LIKE', "%{$query}%")
                        ->orWhere('ville', 'LIKE', "%{$query}%");
                })
                ->select([
                    'id_user',
                    'nom',
                    'prenom',
                    'email',
                    'ville',
                    'quartier',
                    'specialite',
                    'lieu_de_travail',
                    'numero_telephone',
                    'latitude_lieu_de_travail as latitude',
                    'longitude_lieu_de_travail as longitude'
                ])
                ->limit(20)
                ->get()
                ->map(function($medecin) {
                    return [
                        'id_user' => $medecin->id_user,
                        'nom' => $medecin->nom,
                        'prenom' => $medecin->prenom,
                        'email' => $medecin->email,
                        'ville' => $medecin->ville,
                        'quartier' => $medecin->quartier ?? '',
                        'specialite' => $medecin->specialite,
                        'lieu_de_travail' => $medecin->lieu_de_travail,
                        'numero_telephone' => $medecin->numero_telephone,
                        'latitude' => $medecin->latitude,
                        'longitude' => $medecin->longitude,
                        // Propriétés par défaut pour l'affichage Flutter
                        'note' => round(4.5 + (rand(0, 5) / 10), 1),
                        'nombre_consultations' => rand(50, 300),
                        'disponible' => rand(0, 10) > 2, // 80% de chance d'être disponible
                        'prochain_creneau' => null
                    ];
                });

            Log::info('Recherche médecins:', ['query' => $query, 'results' => $medecins->count()]);

            return response()->json([
                'data' => $medecins,
                'message' => 'Recherche effectuée avec succès'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erreur recherche médecins:', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Erreur lors de la recherche',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * CORRECTION 4: Récupérer les médecins recommandés avec logique améliorée
     */
    public function getRecommandes(): JsonResponse
    {
        try {
            // Récupérer les médecins les mieux notés ou récents
            $medecins = User::where('role', 'medecin')
                ->where('statut_compte', 1) // CORRECTION: Nom de colonne corrigé
                ->select([
                    'id_user',
                    'nom',
                    'prenom',
                    'email',
                    'ville',
                    'quartier',
                    'specialite',
                    'lieu_de_travail',
                    'numero_telephone',
                    'latitude_lieu_de_travail as latitude',
                    'longitude_lieu_de_travail as longitude',
                    'created_at'
                ])
                ->orderBy('created_at', 'desc') // Les plus récents en premier
                ->limit(5)
                ->get()
                ->map(function($medecin, $index) {
                    $notes = [4.9, 4.8, 4.7, 4.6, 4.5];
                    return [
                        'id_user' => $medecin->id_user,
                        'nom' => $medecin->nom,
                        'prenom' => $medecin->prenom,
                        'email' => $medecin->email,
                        'ville' => $medecin->ville,
                        'quartier' => $medecin->quartier ?? '',
                        'specialite' => $medecin->specialite,
                        'lieu_de_travail' => $medecin->lieu_de_travail,
                        'numero_telephone' => $medecin->numero_telephone,
                        'latitude' => $medecin->latitude,
                        'longitude' => $medecin->longitude,
                        // Notes décroissantes pour les recommandés
                        'note' => $notes[$index] ?? 4.5,
                        'nombre_consultations' => rand(150, 400),
                        'disponible' => true, // Les recommandés sont généralement disponibles
                        'prochain_creneau' => null
                    ];
                });

            Log::info('Médecins recommandés récupérés:', ['count' => $medecins->count()]);

            return response()->json([
                'data' => $medecins,
                'message' => 'Médecins recommandés récupérés avec succès'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erreur médecins recommandés:', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Erreur lors de la récupération des recommandations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * CORRECTION 5: Display specific medecin with proper mapping
     */
    public function showMedecin(string $id): JsonResponse
    {
        try {
            $medecin = User::where('id_user', $id)
                ->where('role', 'medecin')
                ->first();

            if (!$medecin) {
                return response()->json([
                    'message' => 'Médecin non trouvé'
                ], 404);
            }

            // Mapping pour correspondre au modèle Flutter
            $medecinData = [
                'id_user' => $medecin->id_user,
                'nom' => $medecin->nom,
                'prenom' => $medecin->prenom,
                'email' => $medecin->email,
                'ville' => $medecin->ville,
                'quartier' => $medecin->quartier ?? '',
                'specialite' => $medecin->specialite,
                'lieu_de_travail' => $medecin->lieu_de_travail,
                'numero_telephone' => $medecin->numero_telephone,
                'latitude' => $medecin->latitude_lieu_de_travail,
                'longitude' => $medecin->longitude_lieu_de_travail,
                'date_de_naissance' => $medecin->date_de_naissance,
                'sexe' => $medecin->sexe,
                'numero_onmc' => $medecin->numero_onmc,
                // Propriétés par défaut pour l'affichage
                'note' => 4.8,
                'nombre_consultations' => rand(100, 250),
                'disponible' => true,
                'prochain_creneau' => null
            ];

            return response()->json([
                'data' => $medecinData, // CORRECTION: Structure unifiée
                'medecin' => $medecinData, // Garder pour compatibilité
                'status' => 'success'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erreur récupération médecin:', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Erreur lors de la récupération du médecin',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer les créneaux disponibles d'un médecin
     */
    public function getCreneauxDisponibles(string $id, Request $request): JsonResponse
    {
        $date = $request->get('date', date('Y-m-d'));

        try {
            // Vérifier que le médecin existe
            $medecin = User::where('id_user', $id)->where('role', 'medecin')->first();
            if (!$medecin) {
                return response()->json([
                    'message' => 'Médecin non trouvé'
                ], 404);
            }

            // Pour l'exemple, créer des créneaux fictifs
            $creneaux = [];
            $heures = [
                '08:00-08:30', '08:30-09:00', '09:00-09:30', '09:30-10:00',
                '10:00-10:30', '10:30-11:00', '11:00-11:30', '11:30-12:00',
                '14:00-14:30', '14:30-15:00', '15:00-15:30', '15:30-16:00',
                '16:00-16:30', '16:30-17:00', '17:00-17:30', '17:30-18:00'
            ];

            foreach ($heures as $index => $heure) {
                $times = explode('-', $heure);
                $creneaux[] = [
                    'id' => $index + 1,
                    'heure_debut' => $times[0],
                    'heure_fin' => $times[1],
                    'disponible' => rand(0, 10) > 3, // 70% de disponibilité
                    'date' => $date
                ];
            }

            return response()->json([
                'data' => $creneaux,
                'message' => 'Créneaux récupérés avec succès'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erreur créneaux:', ['medecin_id' => $id, 'error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Erreur lors de la récupération des créneaux',
                'error' => $e->getMessage()
            ], 500);
        }
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
                'email' => 'required|email|unique:users,email,' . $medecin->id_user . ',id_user', // CORRECTION: Table corrigée
                'date_de_naissance' => 'required',
                'sexe' => 'required|string|in:masculin,feminin',
                'numero_telephone' => 'required|max:15', // CORRECTION: Augmenté la limite
                'specialite' => 'required|max:50', // CORRECTION: Nom corrigé
                'numero_onmc'=> 'required|max:15', // CORRECTION: Augmenté la limite
                'lieu_de_travail'=> 'required|string|max:100',
                'ville'=> 'required|string|max:50',
                'password' => 'nullable|string|min:8|confirmed', // CORRECTION: Optionnel
            ]);

        if ($validateData->fails()) {
            return response()->json([
                'message' => 'Données invalides',
                'errors' => $validateData->errors()
            ], 400);
        }

        try {
            // Ne pas inclure le password s'il n'est pas fourni
            $dataToUpdate = $request->except(['password', 'password_confirmation']);
            if ($request->filled('password')) {
                $dataToUpdate['password'] = bcrypt($request->password);
            }

            $medecin->update($dataToUpdate);

            return response()->json([
                'medecin' => $medecin,
                'message'=> 'Médecin modifié avec succès',
            ], 200);
        }catch (\Exception $exception) {
            Log::error('Erreur update médecin:', ['id' => $id, 'error' => $exception->getMessage()]);
            return response()->json([
                'message' => 'Erreur lors de la modification',
                'error' => $exception->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $medecin = User::findorFail($id);
            $medecin->delete();

            return response()->json([
                'message'=> 'Médecin supprimé avec succès',
                'data'=> $medecin,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erreur suppression médecin:', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Erreur lors de la suppression',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Liste des documents d'un médecin
     */
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
            Log::error('Erreur documents médecin:', ['id' => $id, 'error' => $exception->getMessage()]);
            return response()->json([
                'message' => 'Erreur lors de la récupération des documents',
                'error' => $exception->getMessage()
            ], 500);
        }
    }
}
