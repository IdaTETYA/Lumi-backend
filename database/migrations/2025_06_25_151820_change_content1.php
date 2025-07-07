<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Renommer la colonne 'statut' en 'role'
        Schema::table('message', function (Blueprint $table) {
            // Supprimer la contrainte de clé étrangère sur 'chat_id' si elle existe
            $table->dropForeign(['chat_id']);
            // Supprimer la colonne 'chat_id'
            $table->dropColumn('chat_id');
        });

        // Modifier la colonne 'statut' pour la renommer en 'role' et utiliser ENUM
        DB::statement("ALTER TABLE message CHANGE statut role ENUM('user', 'bot') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restaurer la colonne 'chat_id'
        Schema::table('message', function (Blueprint $table) {
            $table->string('chat_id')->nullable();
            $table->foreign('chat_id')->references('id_chat')->on('chat')->onDelete('cascade');
        });

        // Restaurer la colonne 'role' en 'statut' avec type string
        DB::statement("ALTER TABLE message CHANGE role statut VARCHAR(255) NOT NULL");
    }
};
