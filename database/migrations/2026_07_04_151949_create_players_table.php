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
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('country');
            $table->string('country_code', 2);
            $table->date('date_of_birth');
            $table->integer('height_cm')->nullable();
            $table->enum('dominant_hand', ['Left', 'Right']);
            $table->enum('playing_style', ['Offensive', 'Defensive', 'All-round'])->nullable();
            $table->integer('world_ranking')->nullable();
            $table->integer('rating_points')->nullable();
            $table->timestamps();

            $table->index('country_code');
            $table->index('world_ranking');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('players');
    }
};
