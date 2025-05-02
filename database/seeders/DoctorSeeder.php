<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DoctorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('fr_FR'); // Utilise Faker avec la localisation française

        // Liste des spécialités médicales
        $specialites = [
            'Cardiologie',
            'Pédiatrie',
            'Dermatologie',
            'Neurologie',
            'Chirurgie générale',
            'Ophtalmologie',
            'Gynécologie',
            'Psychiatrie',
            'Orthopédie',
            'Médecine générale'
        ];

        // Liste des lieux de travail
        $lieuxDeTravail = [
            'Hôpital Central de Paris',
            'Clinique Saint-Jean',
            'Hôpital Necker',
            'Clinique des Lilas',
            'Hôpital Bichat',
            'Centre Médical Pasteur',
            'Hôpital Cochin',
            'Clinique Montmartre',
            'Hôpital Lariboisière',
            'Centre de Santé Voltaire'
        ];

        // Créer 10 médecins
        for ($i = 40; $i <= 50; $i++) {
            $nom = $faker->lastName;
            $prenom = $faker->firstName;
            $idUser = 'USER' . str_pad($i, 3, '0', STR_PAD_LEFT); // Exemple : USER001, USER002, ...

            User::create([
                'id_user' => $idUser,
                'nom' => $nom,
                'prenom' => $prenom,
                'date_de_naissance' => $faker->dateTimeBetween('-60 years', '-30 years')->format('Y-m-d'), // Âge entre 30 et 60 ans
                'sexe' => $faker->randomElement(['masculin', 'feminin']),
                'ville' => $faker->city,
                'quartier' => $faker->streetName,
                'numero_telephone' => $faker->phoneNumber,
                'email' => strtolower($prenom . '.' . $nom . '@example.com'),
                'password' => Hash::make('password'), // Mot de passe par défaut
                'role' => 'medecin',
                'specialite' => $faker->randomElement($specialites),
                'numero_onmc' => 'ONMC' . $faker->unique()->numberBetween(100, 999), // Numéro ONMC unique
                'lieu_de_travail' => $faker->randomElement($lieuxDeTravail),
                'latitude_lieu_de_travail' => $faker->latitude(48.8, 48.9), // Latitude autour de Paris
                'longitude_lieu_de_travail' => $faker->longitude(2.2, 2.4), // Longitude autour de Paris
                'statut_compte' => $faker->randomElement([0, 1, -1]),
                'est_connecte' => false,
                'device_token' => null,
                'recevoir_notifications' => $faker->boolean(80), // 80% de chances d'accepter les notifications
                'theme' => $faker->randomElement(['light', 'dark']),
                'derniere_connexion' => $faker->dateTimeThisYear()->format('Y-m-d H:i:s'),
                'email_verifie_at' => $faker->dateTimeThisYear()->format('Y-m-d H:i:s'),
                'accepte_conditions' => true,
                'derniere_activite' => $faker->dateTimeThisMonth()->format('Y-m-d H:i:s'),
                'nombre_connexions' => $faker->numberBetween(1, 100),
                'created_at' => $faker->dateTimeThisYear()->format('Y-m-d H:i:s'),
                'updated_at' => $faker->dateTimeThisYear()->format('Y-m-d H:i:s'),
                'motif_refus' => $faker->randomElement(['', 'Documents manquants', 'Numéro ONMC invalide']),
            ]);
        }
    }
}
