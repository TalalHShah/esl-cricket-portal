<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auction_sessions', function (Blueprint $table) {
            $table->timestamp('bid_deadline_at')->nullable()->after('highest_bidder_team_id');
        });
    }

    public function down(): void
    {
        Schema::table('auction_sessions', function (Blueprint $table) {
            $table->dropColumn('bid_deadline_at');
        });
    }
};
