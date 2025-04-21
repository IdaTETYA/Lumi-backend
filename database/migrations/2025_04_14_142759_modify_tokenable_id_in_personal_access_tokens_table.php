<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyTokenableIdInPersonalAccessTokensTable extends Migration
{
    public function up()
    {
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            // Modifier la colonne tokenable_id pour qu'elle soit une chaîne de caractères
            $table->string('tokenable_id')->change();
        });
    }

    public function down()
    {
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            // Revenir à un unsignedBigInteger si besoin
            $table->unsignedBigInteger('tokenable_id')->change();
        });
    }
}
