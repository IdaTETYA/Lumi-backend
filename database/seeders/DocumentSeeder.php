<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\User;
use Carbon\Carbon;
use Random\RandomException;

class DocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     * @throws RandomException
     */
    public function run()
    {
        // Récupérer les médecins et administrateurs existants
        $medecins = User::where('role', 'medecin')->get();
        $admins = User::where('role', 'administrateur')->get();

        // Types de documents possibles
        $documentTypes = ['pdf', 'jpg', 'png', 'docx'];
        $documentTitles = [
            'Diplôme de médecine',
            'Certificat ONMC',
            'Photo d\'identité',
            'Attestation de travail',
            'Curriculum Vitae',
        ];

        // Générer 10 documents fictifs
        $documents = [];
        for ($i = 0; $i < 10; $i++) {
            $medecin = $medecins->random();
            $admin = $admins->random();
            $status = random_int(-1, 1); // -1: rejeté, 0: en attente, 1: validé
            $type = $documentTypes[array_rand($documentTypes)];
            $title = $documentTitles[array_rand($documentTitles)];

            $documents[] = [
                'id_document' => Str::uuid()->toString(),
                'titre' => $title,
                'type' => $type,
                'file' => "documents/{$medecin->id_user}/{$title}.{$type}", // Chemin fictif
                'medecin_id' => $medecin->id_user,
                'valide_par_id' => $status === 1 ? $admin->id_user : null, // Validé par un admin si statut = 1
                'statut' => $status,
                'created_at' => Carbon::now()->subDays(rand(1, 30)),
                'updated_at' => Carbon::now(),
            ];
        }

        // Insérer les données dans la table
        DB::table('document')->insert($documents);
    }
}
