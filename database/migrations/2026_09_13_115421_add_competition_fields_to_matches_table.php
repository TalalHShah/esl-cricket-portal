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
        Schema::table('matches', function (Blueprint $table) {
            $table->foreignId('competition_id')->nullable()->after('id')->constrained()->nullOnDelete();
            // 'league', 'playoff1', 'playoff2', 'qualifier2', 'final'
            $table->string('stage')->nullable()->after('competition_id');
            $table->unsignedTinyInteger('leg')->nullable()->after('stage');

            // Team scorelines, needed for Net Run Rate standings. Overs
            // are stored in cricket notation (19.4 = 19 overs, 4 balls);
            // convert with CricketMatch::oversToFloat() before doing NRR
            // math. *_all_out means the side was bowled out before using
            // its full quota — NRR then credits them the full overs
            // allotment instead of the (shorter) overs actually faced.
            $table->unsignedSmallInteger('home_runs')->nullable();
            $table->decimal('home_overs', 4, 1)->nullable();
            $table->boolean('home_all_out')->default(false);
            $table->unsignedSmallInteger('away_runs')->nullable();
            $table->decimal('away_overs', 4, 1)->nullable();
            $table->boolean('away_all_out')->default(false);

            $table->index(['competition_id', 'stage']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->dropConstrainedForeignId('competition_id');
            $table->dropColumn([
                'stage', 'leg',
                'home_runs', 'home_overs', 'home_all_out',
                'away_runs', 'away_overs', 'away_all_out',
            ]);
        });
    }
};
