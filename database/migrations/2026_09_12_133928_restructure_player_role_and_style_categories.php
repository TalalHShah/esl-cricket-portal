<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Collapses the old 5-way role enum (Batsman, Wicketkeeper,
     * All-rounder, Fast Bowler, Spinner) into the 4 real cricket
     * categories (Batsman, All-rounder, Bowler, Wicketkeeper), with the
     * pace/spin distinction moving to bowling_style where it belongs
     * alongside batting hand and bowling arm.
     */
    public function up(): void
    {
        // Step 1: widen the enum so both old and new values are valid
        // while we backfill — MySQL rejects writing 'Bowler' into a
        // column that doesn't yet list it as an allowed enum value.
        DB::statement("ALTER TABLE players MODIFY role ENUM('Batsman','Wicketkeeper','All-rounder','Fast Bowler','Spinner','Bowler') NOT NULL DEFAULT 'Batsman'");

        // Step 2: fold Fast Bowler / Spinner into Bowler.
        DB::table('players')->whereIn('role', ['Fast Bowler', 'Spinner'])->update(['role' => 'Bowler']);

        // Step 3: narrow the enum to the final 4 categories.
        DB::statement("ALTER TABLE players MODIFY role ENUM('Batsman','All-rounder','Bowler','Wicketkeeper') NOT NULL DEFAULT 'Batsman'");

        // Step 4: normalise existing free-text batting/bowling style
        // values to the canonical dropdown options used going forward.
        $battingMap = [
            'Right-hand bat' => 'Right-Hand Bat',
            'Left-hand bat' => 'Left-Hand Bat',
        ];

        foreach ($battingMap as $old => $new) {
            DB::table('players')->where('batting_style', $old)->update(['batting_style' => $new]);
        }

        $bowlingMap = [
            'Right-arm fast' => 'Right-arm Fast',
            'Right-arm fast-medium' => 'Right-arm Fast-Medium',
            'Left-arm fast' => 'Left-arm Fast',
            'Slow left-arm orthodox' => 'Left-arm Orthodox',
            'Off break' => 'Right-arm Off Spin',
            'Leg break' => 'Right-arm Leg Spin',
        ];

        foreach ($bowlingMap as $old => $new) {
            DB::table('players')->where('bowling_style', $old)->update(['bowling_style' => $new]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE players MODIFY role ENUM('Batsman','Wicketkeeper','All-rounder','Fast Bowler','Spinner','Bowler') NOT NULL DEFAULT 'Batsman'");

        // Best-effort reversal: we can't know which Bowlers were
        // originally Fast Bowler vs Spinner, so infer from bowling_style.
        DB::table('players')
            ->where('role', 'Bowler')
            ->where(function ($q) {
                $q->where('bowling_style', 'like', '%Spin%')
                    ->orWhere('bowling_style', 'like', '%Orthodox%')
                    ->orWhere('bowling_style', 'like', '%Chinaman%');
            })
            ->update(['role' => 'Spinner']);

        DB::table('players')->where('role', 'Bowler')->update(['role' => 'Fast Bowler']);

        DB::statement("ALTER TABLE players MODIFY role ENUM('Batsman','Wicketkeeper','All-rounder','Fast Bowler','Spinner') NOT NULL DEFAULT 'Batsman'");
    }
};
