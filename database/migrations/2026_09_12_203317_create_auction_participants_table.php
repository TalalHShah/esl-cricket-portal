<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tracks which teams have taken a seat in an auction room, so
     * everyone can see who's present (and who's gone quiet) in
     * real time via a presence heartbeat.
     */
    public function up(): void
    {
        Schema::create('auction_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auction_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->dateTime('joined_at');
            $table->dateTime('last_seen_at');
            $table->timestamps();

            $table->unique(['auction_session_id', 'team_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auction_participants');
    }
};
