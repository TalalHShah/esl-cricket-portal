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
        Schema::table('auction_drafts', function (Blueprint $table) {
            $table->json('passed_team_ids')->nullable()->after('consecutive_skips');
            $table->timestamp('turn_deadline_at')->nullable()->after('passed_team_ids');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('auction_drafts', function (Blueprint $table) {
            $table->dropColumn(['passed_team_ids', 'turn_deadline_at']);
        });
    }
};
