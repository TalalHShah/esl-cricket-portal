<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auction_sessions', function (Blueprint $table) {
            $table->foreignId('auction_draft_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->foreignId('nominated_by_team_id')->nullable()->after('highest_bidder_team_id')->constrained('teams')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('auction_sessions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('auction_draft_id');
            $table->dropConstrainedForeignId('nominated_by_team_id');
        });
    }
};
