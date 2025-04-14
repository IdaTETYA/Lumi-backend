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
        Schema::create('participe_a_chat', function (Blueprint $table) {
            $table->uuid('id_participe_a_chat')->primary();
            $table->uuid('utilisateur_id');
            $table->uuid('chat_id');
            $table->string('role');
            $table->datetime('date_participation');
            $table->boolean('actif')->default(true);
            $table->foreign('utilisateur_id')->references('id_user')->on('user')->onDelete('cascade');
            $table->foreign('chat_id')->references('id_chat')->on('chat')->onDelete('cascade');
            $table->index(['utilisateur_id', 'chat_id']);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('participe_a_chat');
    }
};
