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
        Schema::create('type_notification', function (Blueprint $table) {
            $table->uuid('id_type_notification')->primary(); // UUID, stocké comme string
            $table->string('nom')->unique(); // Ex. "nouveau_message", "rappel_consultation"
            $table->string('description')->nullable(); // Description du type
            $table->string('icone')->nullable(); // Icône associée (ex. pour l'interface)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('type_notification');
    }
};
