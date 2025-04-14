<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user', function (Blueprint $table) {
                $table->uuid('id_user')->primary();
                $table->string('nom');
                $table->string('prenom');
                $table->date('date_de_naissance')->nullable();
                $table->string('sexe')->nullable();
                $table->string('ville')->nullable();
                $table->string('quartier')->nullable();
                $table->string('numero_telephone')->nullable();
                $table->string('email')->unique();
                $table->string('password');
                $table->string('role');

                $table->string('specialite')->nullable();
                $table->string('numero_onmc')->nullable();
                $table->string('lieu_de_travail')->nullable();
                $table->double('latitude_lieu_de_travail')->nullable();
                $table->double('longitude_lieu_de_travail')->nullable();
                $table->string('motif_refus')->nullable();

                $table->integer('stade_de_grossesse')->nullable();

                $table->boolean('est_connecte')->default(false);
                $table->string('device_token')->nullable();
                $table->boolean('recevoir_notifications')->default(true);
                $table->string('theme')->nullable();
                $table->dateTime('derniere_connexion')->nullable();
                $table->dateTime('email_verifie_at')->nullable();
                $table->string('remember_token')->nullable();
                $table->boolean('accepte_conditions')->default(false);
                $table->dateTime('derniere_activite')->nullable();
                $table->integer('nombre_connexions')->default(0);
                $table->boolean('statut_compte')->nullable();
                $table->string('photo')->nullable();
                $table->string('motif_banis')->nullable();
                $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
