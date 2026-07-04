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
        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained()->cascadeOnDelete();
            $table->foreignId('player_a_id')->constrained('players')->cascadeOnDelete();
            $table->foreignId('player_b_id')->constrained('players')->cascadeOnDelete();
            $table->foreignId('winner_id')->nullable()->constrained('players')->nullOnDelete();
            $table->unsignedSmallInteger('player_a_sets')->default(0);
            $table->unsignedSmallInteger('player_b_sets')->default(0);
            $table->date('match_date');
            $table->time('match_time')->nullable();
            $table->string('round');
            $table->enum('status', ['Scheduled', 'Completed', 'Walkover', 'Cancelled'])->default('Scheduled');
            $table->timestamps();

            $table->index('match_date');
            $table->index('status');
            $table->index(['player_a_id', 'player_b_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};
