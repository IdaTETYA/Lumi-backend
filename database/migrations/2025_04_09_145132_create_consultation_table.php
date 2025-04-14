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
        Schema::create('consultation', function (Blueprint $table) {
            $table->uuid('id_consultation')->primary();
            $table->uuid('patient_id');
            $table->uuid('medecin_id');
            $table->uuid('rendez_vous_id')->nullable();
            $table->dateTime('date');
            $table->time('heure_debut');
            $table->time('heure_fin');
            $table->string('motif');
            $table->string('description');
            $table->uuid('type_consultation_id');
            $table->boolean('statut');
            $table->foreign('patient_id')->references('id_user')->on('user')->onDelete('cascade');
            $table->foreign('medecin_id')->references('id_user')->on('user')->onDelete('cascade');
            $table->foreign('rendez_vous_id')->references('id_rendez_vous')->on('rendez_vous')->onDelete('set null');
            $table->foreign('type_consultation_id')->references('id_type_consultation')->on('type_consultation')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultation');
    }
};
