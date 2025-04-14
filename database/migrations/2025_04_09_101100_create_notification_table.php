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
        Schema::create('notification', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('type_notification_id'); // UUID, stocké comme string, référence à notification_types            $table->text('message')->nullable();
            $table->uuid('destinataire_id');
            $table->uuid('expediteur_id')->nullable();
            $table->string('statut');
            $table->foreign('type_notification_id')->references('id_type_notification')->on('type_notification')->onDelete('cascade');
            $table->foreign('destinataire_id')->references('id_user')->on('user')->onDelete('cascade');
            $table->foreign('expediteur_id')->references('id_user')->on('user')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification');
    }
};
