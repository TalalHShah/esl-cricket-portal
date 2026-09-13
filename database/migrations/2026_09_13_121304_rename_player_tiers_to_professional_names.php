<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Renames the player tier scale to the standard cricket-auction
 * naming (Platinum / Diamond / Gold / Silver) instead of the old
 * Superstar / Star / Normal / Low-value labels — same 4 ranks, same
 * meaning, just more professional wording. Widens the enum to hold
 * both old and new values while remapping existing rows, then
 * narrows it to just the new set.
 */
return new class extends Migration
{
    private const MAP = [
        'Superstar' => 'Platinum',
        'Star' => 'Diamond',
        'Normal' => 'Gold',
        'Low-value' => 'Silver',
    ];

    public function up(): void
    {
        DB::statement("ALTER TABLE players MODIFY tier ENUM('Superstar','Star','Normal','Low-value','Platinum','Diamond','Gold','Silver') NOT NULL DEFAULT 'Normal'");

        foreach (self::MAP as $old => $new) {
            DB::table('players')->where('tier', $old)->update(['tier' => $new]);
        }

        DB::statement("ALTER TABLE players MODIFY tier ENUM('Platinum','Diamond','Gold','Silver') NOT NULL DEFAULT 'Gold'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE players MODIFY tier ENUM('Superstar','Star','Normal','Low-value','Platinum','Diamond','Gold','Silver') NOT NULL DEFAULT 'Normal'");

        foreach (array_flip(self::MAP) as $new => $old) {
            DB::table('players')->where('tier', $new)->update(['tier' => $old]);
        }

        DB::statement("ALTER TABLE players MODIFY tier ENUM('Superstar','Star','Normal','Low-value') NOT NULL DEFAULT 'Normal'");
    }
};
