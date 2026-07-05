<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['players', 'tournaments', 'matches'] as $table) {
            if (Schema::hasColumn($table, 'statstt_id')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropColumn('statstt_id');
                });
            }
        }
    }

    public function down(): void
    {
        // No-op: statstt_id column will not be recreated.
    }
};
