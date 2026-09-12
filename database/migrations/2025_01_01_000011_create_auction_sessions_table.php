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
        Schema::create('auction_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('status', [
                'scheduled',
                'live',
                'paused',
                'completed',
                'cancelled',
            ])->default('scheduled');
            $table->foreignId('player_id')->nullable()->constrained('players')->nullOnDelete();
            $table->foreignId('current_team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->decimal('current_bid', 14, 2)->default(0);
            $table->decimal('starting_bid', 14, 2)->default(0);
            $table->decimal('bid_increment', 14, 2)->default(100000);
            $table->foreignId('highest_bidder_team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->foreignId('started_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('player_id');
            $table->index('current_team_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auction_sessions');
    }
};
