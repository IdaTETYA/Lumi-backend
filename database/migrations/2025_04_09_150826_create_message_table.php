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
        Schema::create('message', function (Blueprint $table) {
            $table->uuid('id_message')->primary();
            $table->uuid('chat_ai_id')->nullable();
            $table->string('content');
            $table->string('statut');
            $table->string('user_id');
            $table->string('chat_id');
            $table->foreign('user_id')->references('id_user')->on('user')->onDelete('cascade');
            $table->foreign('chat_id')->references('id_chat')->on('chat')->onDelete('cascade');
            $table->foreign('chat_ai_id')->references('id_chat_ai')->on('chat_ai')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message');
    }
};
