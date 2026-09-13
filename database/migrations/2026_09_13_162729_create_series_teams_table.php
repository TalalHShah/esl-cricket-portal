<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('series_teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('series_id')->constrained('series')->cascadeOnDelete();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->enum('status', ['invited', 'accepted', 'declined'])->default('invited');
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->unique(['series_id', 'team_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('series_teams');
    }
};
