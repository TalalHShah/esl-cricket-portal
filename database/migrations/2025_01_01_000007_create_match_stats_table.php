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
        Schema::create('match_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained('matches')->cascadeOnDelete();
            $table->foreignId('player_id')->constrained('players')->cascadeOnDelete();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->unsignedInteger('runs_scored')->default(0);
            $table->unsignedInteger('balls_faced')->default(0);
            $table->unsignedInteger('fours')->default(0);
            $table->unsignedInteger('sixes')->default(0);
            $table->decimal('overs_bowled', 3, 1)->default(0);
            $table->unsignedInteger('maidens')->default(0);
            $table->unsignedInteger('runs_conceded')->default(0);
            $table->unsignedInteger('wickets_taken')->default(0);
            $table->unsignedInteger('catches')->default(0);
            $table->unsignedInteger('stumpings')->default(0);
            $table->boolean('was_exceptional')->default(false);
            $table->decimal('value_change', 14, 2)->default(0);
            $table->timestamps();

            $table->index('match_id');
            $table->index('player_id');
            $table->index('team_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('match_stats');
    }
};
