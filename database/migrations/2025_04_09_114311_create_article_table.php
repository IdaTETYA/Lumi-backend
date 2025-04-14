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
        Schema::create('article', function (Blueprint $table) {
            $table->uuid('id_article')->primary(); // UUID, stocké comme string
            $table->uuid('supprime_par_id'); // UUID, stocké comme string
            $table->string('categorie'); // UUID, référence à article_categories (optionnel)
            $table->string('titre'); // Titre de l’article
            $table->boolean('statut');
            $table->text('contenu'); // Contenu de l’article (type text pour contenu long)
            $table->uuid('medecin_id'); // UUID, référence à medecins (médecin qui a publié)
            $table->dateTime('date_publication')->nullable(); // Date de publication effective
            $table->string('image')->nullable(); // Chemin ou URL de l’image principale
            $table->text('resume')->nullable(); // Résumé ou extrait de l’article
            $table->foreign('medecin_id')->references('id_user')->on('user')->onDelete('cascade');
            $table->foreign('supprime_par_id')->references('id_user')->on('user')->onDelete('cascade');
            $table->index('medecin_id'); // Index pour les requêtes
            $table->index('supprime_par_id'); // Index pour les requêtes
            $table->timestamps(); // Ajoute created_at et updated_at
            $table->softDeletes(); // Ajoute deleted_at pour le soft delete
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('article');
    }
};
