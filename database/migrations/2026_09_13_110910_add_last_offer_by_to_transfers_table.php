<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transfers', function (Blueprint $table) {
            $table->foreignId('last_offer_by_team_id')->nullable()->after('fee')->constrained('teams')->nullOnDelete();
        });

        DB::table('transfers')->where('status', 'pending')->update(['last_offer_by_team_id' => DB::raw('to_team_id')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transfers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('last_offer_by_team_id');
        });
    }
};
