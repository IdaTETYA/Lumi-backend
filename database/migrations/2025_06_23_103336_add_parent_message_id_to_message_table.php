<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('message', function (Blueprint $table) {
            $table->uuid('parent_message_id')->nullable()->after('chat_ai_id');
            $table->foreign('parent_message_id')->references('id_message')->on('message')->onDelete('set null');
        });
    }

    public function down(): void {
        Schema::table('message', function (Blueprint $table) {
            $table->dropForeign(['parent_message_id']);
            $table->dropColumn('parent_message_id');
        });
    }
};

