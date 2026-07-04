<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->string('statstt_id')->nullable()->unique()->after('id');
        });

        Schema::table('tournaments', function (Blueprint $table) {
            $table->string('statstt_id')->nullable()->unique()->after('id');
        });

        Schema::table('matches', function (Blueprint $table) {
            $table->string('statstt_id')->nullable()->unique()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->dropColumn('statstt_id');
        });

        Schema::table('tournaments', function (Blueprint $table) {
            $table->dropColumn('statstt_id');
        });

        Schema::table('matches', function (Blueprint $table) {
            $table->dropColumn('statstt_id');
        });
    }
};
