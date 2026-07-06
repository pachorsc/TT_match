<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('news');
    }

    public function down(): void
    {
        // Re-create the table if rolled back
        Schema::create('news', function ($table) {
            $table->id();
            $table->foreignId('player_id')->nullable()->constrained()->nullOnDelete();
            $table->string('headline');
            $table->text('summary');
            $table->string('source');
            $table->string('url');
            $table->timestamp('published_at');
            $table->timestamps();
            $table->index('published_at');
        });
    }
};
