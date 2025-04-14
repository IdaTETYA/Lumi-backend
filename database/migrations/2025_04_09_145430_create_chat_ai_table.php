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
        Schema::create('chat_ai', function (Blueprint $table) {
            $table->uuid('id_chat_ai')->primary(); // UUID, stocké comme string
            $table->uuid('patient_id'); // Clé étrangère vers patients
            $table->json('diagnostic'); // Liste de diagnostics (stockée en JSON)
            $table->text('conseil')->nullable(); // Conseil donné par l'IA
            $table->text('analyse')->nullable(); // Analyse des symptômes ou données
            $table->foreign('patient_id')->references('id_user')->on('user')->onDelete('cascade');
            $table->index('patient_id');
            $table->timestamps(); // created_at et updated_at
            $table->softDeletes(); // deleted_at pour le soft delete
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_ai');
    }
};
