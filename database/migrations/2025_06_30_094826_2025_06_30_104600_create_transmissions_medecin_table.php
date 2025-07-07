<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transmissions_medecin', function (Blueprint $table) {
            $table->id('id_transmission'); // Clé primaire auto-incrémentée
            $table->uuid('patient_id'); // Clé étrangère vers user(id_user)
            $table->uuid('chat_ai_id')->nullable(); // Clé étrangère vers chat_ai(id_chat_ai)
            $table->uuid('consultation_id')->nullable(); // Clé étrangère vers consultation(id_consultation)
            $table->json('symptomes'); // Liste des symptômes en JSON
            $table->string('maladie_predite')->nullable(); // Maladie prédite par le modèle ML
            $table->float('confiance')->nullable(); // Confiance de la prédiction
            $table->string('priorite')->default('basse'); // Priorité (basse, moyenne, haute)
            $table->json('rapport_complet'); // Rapport détaillé en JSON
            $table->string('statut')->default('en_attente'); // Statut (ex. en_attente, traité)
            $table->timestamps(); // created_at et updated_at
            $table->softDeletes(); // deleted_at pour soft delete

            // Contraintes de clés étrangères
            $table->foreign('patient_id')
                ->references('id_user')
                ->on('user')
                ->onDelete('cascade');
            $table->foreign('chat_ai_id')
                ->references('id_chat_ai')
                ->on('chat_ai')
                ->onDelete('set null');
            $table->foreign('consultation_id')
                ->references('id_consultation')
                ->on('consultation')
                ->onDelete('set null');

            // Index composite avec un nom court
            $table->index(['patient_id', 'chat_ai_id', 'consultation_id'], 'trans_med_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transmissions_medecin');
    }
};
