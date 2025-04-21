<?php


use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Medecin;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Utilisateur admin
        User::create([
            'id_user' => (string) \Illuminate\Support\Str::uuid(),
            'nom' => 'Admin User',
            'prenom' => 'Jean',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Utilisateur non-admin
        User::create([
            'id_user' => (string) \Illuminate\Support\Str::uuid(),
            'nom' => 'Regular User',
            'prenom' => 'Jean',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'role' => 'patient',
        ]);

        User::create([
            'id_user' => (string) \Illuminate\Support\Str::uuid(),
            'nom' => 'Dupont',
            'prenom' => 'Jean',
            'specialite' => 'Cardiologue',
            'email' => 'jean.dupont@example.com',
            'ville' => '123 Rue de Paris',
            'password' => Hash::make('password'),
            'statut_compte' => 0,
            'role' => 'medecin',
        ]);
    }
}
