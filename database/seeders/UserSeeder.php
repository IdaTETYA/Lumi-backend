<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Vérifier si des médecins et administrateurs existent
        $medecinsCount = DB::table('user')->where('role', 'medecin')->count();
        $adminsCount = DB::table('user')->where('role', 'administrateur')->count();

        // Si aucun médecin ou administrateur n'existe, insérer les données
        if ($medecinsCount === 0 || $adminsCount === 0) {
            $users = [
                [
                    'id_user' => Str::uuid()->toString(),
                    'nom' => 'Dupont',
                    'prenom' => 'Jean',
                    'email' => 'jean.dupont@example.com',
                    'password' => Hash::make('password123'), // Mot de passe haché
                    'numero_telephone' => '0601234567',
                    'role' => 'medecin',
                    'specialite' => 'Cardiologie',
                    'numero_onmc' => 'ONMC12345',
                    'lieu_de_travail' => 'Hôpital de Paris',
                    'statut_compte' => 1,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'id_user' => Str::uuid()->toString(),
                    'nom' => 'Martin',
                    'prenom' => 'Sophie',
                    'email' => 'sophie.martin@example.com',
                    'password' => Hash::make('password123'), // Mot de passe haché
                    'numero_telephone' => '0609876543',
                    'role' => 'medecin',
                    'specialite' => 'Pédiatrie',
                    'numero_onmc' => 'ONMC67890',
                    'lieu_de_travail' => 'Clinique Lyon',
                    'statut_compte' => 0,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'id_user' => Str::uuid()->toString(),
                    'nom' => 'Admin',
                    'prenom' => 'Paul',
                    'email' => 'paul.admin@example.com',
                    'password' => Hash::make('admin123'), // Mot de passe haché
                    'numero_telephone' => '0612345678',
                    'role' => 'administrateur',
                    'specialite' => null,
                    'numero_onmc' => null,
                    'lieu_de_travail' => null,
                    'statut_compte' => 1,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
            ];

            DB::table('user')->insert($users);
            $this->command->info('Utilisateurs (médecins et administrateurs) insérés avec succès.');
        } else {
            $this->command->info('Des médecins et administrateurs existent déjà. Aucune insertion effectuée.');
        }
    }
}
