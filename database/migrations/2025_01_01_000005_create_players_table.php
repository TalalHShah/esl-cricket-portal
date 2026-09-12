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
            $table->string('name');
            $table->string('country', 64);
            $table->enum('role', [
                'Batsman',
                'Wicketkeeper',
                'All-rounder',
                'Fast Bowler',
                'Spinner',
            ])->default('Batsman');
            $table->enum('tier', [
                'Superstar',
                'Star',
                'Normal',
                'Low-value',
            ])->default('Normal');
            $table->decimal('base_value', 14, 2)->default(0);
            $table->decimal('current_value', 14, 2)->default(0);
            $table->decimal('real_life_form_modifier', 5, 2)->default(1.00);
            $table->unsignedTinyInteger('age')->nullable();
            $table->string('batting_style', 32)->nullable();
            $table->string('bowling_style', 64)->nullable();
            $table->string('image')->nullable();
            $table->foreignId('team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->decimal('sold_price', 14, 2)->nullable();
            $table->boolean('is_auctioned')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('role');
            $table->index('tier');
            $table->index('team_id');
            $table->index('country');
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
