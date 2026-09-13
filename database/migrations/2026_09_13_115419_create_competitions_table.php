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
        Schema::create('competitions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->enum('type', ['league', 'cup'])->default('league');
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('rounds')->default(1); // 1 = single round-robin, 2 = double
            $table->unsignedTinyInteger('total_overs')->default(20);
            $table->boolean('has_playoffs')->default(true);
            $table->unsignedSmallInteger('points_win')->default(2);
            $table->unsignedSmallInteger('points_tie')->default(1);
            $table->unsignedSmallInteger('points_loss')->default(0);
            $table->enum('status', ['draft', 'league_stage', 'playoffs', 'completed'])->default('draft');
            $table->date('starts_on')->nullable();
            $table->unsignedTinyInteger('fixture_interval_days')->default(3);
            $table->boolean('is_official')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competitions');
    }
};
