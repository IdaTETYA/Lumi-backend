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
        Schema::create('rendez_vous', function (Blueprint $table) {
            $table->uuid('id_rendez_vous')->primary(); // UUID, stocké comme string
            $table->uuid('patient_id'); // UUID, référence à patients
            $table->uuid('medecin_id'); // UUID, référence à medecins
            $table->dateTime('date'); // Date et heure du rendez-vous
            $table->string('statut'); // Ex. "en_attente", "confirme", "annule"
            $table->foreign('patient_id')->references('id_user')->on('user')->onDelete('cascade');
            $table->foreign('medecin_id')->references('id_user')->on('user')->onDelete('cascade');
            $table->index('patient_id');
            $table->index('medecin_id');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rendez_vous');
    }
};
