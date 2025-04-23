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
        Schema::create('document', function (Blueprint $table) {
            $table->uuid('id_document')->primary(); // UUID, stocké comme string
            $table->string('titre'); // Titre du document
            $table->string('type');
            $table->string('file'); // Chemin ou URL du fichier
            $table->uuid('medecin_id'); // UUID, référence à medecins (médecin qui a fourni le document)
            $table->uuid('valide_par_id')->nullable(); // UUID, référence à administrateurs (administrateur qui a validé)
            $table->boolean('statut');
            $table->foreign('medecin_id')->references('id_user')->on('user')->onDelete('cascade');
            $table->foreign('valide_par_id')->references('id_user')->on('user')->onDelete('set null');
            $table->index('medecin_id'); // Index pour les requêtes fréquentes
            $table->timestamps(); // mute created_at et updated_at
            $table->softDeletes(); // Ajoute deleted_at pour le soft delete
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document');
    }
};
