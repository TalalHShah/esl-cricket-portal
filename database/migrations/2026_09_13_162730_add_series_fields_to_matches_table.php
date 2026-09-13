<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->foreignId('series_id')->nullable()->after('competition_id')->constrained('series')->nullOnDelete();
            $table->enum('match_format', ['T10', 'T20', 'ODI', 'Test'])->nullable()->after('series_id');
            $table->string('venue')->nullable()->after('match_format');
        });
    }

    public function down(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->dropConstrainedForeignId('series_id');
            $table->dropColumn(['match_format', 'venue']);
        });
    }
};
