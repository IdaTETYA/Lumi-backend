<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_ai', function (Blueprint $table) {
            $table->uuid('consultation_id')->nullable()->after('patient_id');
            $table->foreign('consultation_id')
                ->references('id_consultation')
                ->on('consultation')
                ->onDelete('cascade');
            $table->index('consultation_id');
        });
    }

    public function down(): void
    {
        Schema::table('chat_ai', function (Blueprint $table) {
            $table->dropForeign(['consultation_id']);
            $table->dropColumn('consultation_id');
        });
    }
};
