<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->string('ittf_id')->nullable()->unique()->after('wtt_id');
        });

        Schema::table('tournaments', function (Blueprint $table) {
            $table->string('ittf_id')->nullable()->unique()->after('statstt_id');
        });

        Schema::table('matches', function (Blueprint $table) {
            $table->string('ittf_id')->nullable()->unique()->after('statstt_id');
        });
    }

    public function down(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->dropColumn('ittf_id');
        });

        Schema::table('tournaments', function (Blueprint $table) {
            $table->dropColumn('ittf_id');
        });

        Schema::table('matches', function (Blueprint $table) {
            $table->dropColumn('ittf_id');
        });
    }
};
