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
        Schema::table('auction_sessions', function (Blueprint $table) {
            // Snapshot of the player's tier at the moment they were
            // nominated during the draft — stays stable for auction
            // ordering (Platinum -> Diamond -> Gold -> Silver) even if
            // an admin edits the player's tier afterward.
            $table->string('tier')->nullable()->after('player_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('auction_sessions', function (Blueprint $table) {
            $table->dropColumn('tier');
        });
    }
};
