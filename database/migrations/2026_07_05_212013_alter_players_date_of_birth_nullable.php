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
        Schema::table('players', function (Blueprint $table) {
            $table->date('date_of_birth')->nullable()->change();
        });

        // Null out fake DOBs so ages aren't displayed incorrectly
        DB::table('players')
            ->whereIn('date_of_birth', ['2000-01-01', '1970-01-01'])
            ->update(['date_of_birth' => null]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->date('date_of_birth')->nullable(false)->change();
        });
    }
};
