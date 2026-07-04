<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('players')
            ->whereNull('world_ranking')
            ->delete();
    }

    public function down(): void
    {
        // This operation cannot be reversed — deleted data cannot be restored.
    }
};
