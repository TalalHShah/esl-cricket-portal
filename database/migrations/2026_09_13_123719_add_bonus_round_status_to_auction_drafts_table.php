<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Adds a 'bonus_round' status: once normal turn-based picking ends
 * (every country retired), the admin can optionally open a free-for-all
 * round where any manager can pick any country/player with no turn
 * order or timer, in case squads still aren't full enough to auction
 * a proper pool from.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE auction_drafts MODIFY status ENUM('setup','active','bonus_round','completed') NOT NULL DEFAULT 'setup'");
    }

    public function down(): void
    {
        DB::table('auction_drafts')->where('status', 'bonus_round')->update(['status' => 'active']);
        DB::statement("ALTER TABLE auction_drafts MODIFY status ENUM('setup','active','completed') NOT NULL DEFAULT 'setup'");
    }
};
