<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auction_participants', function (Blueprint $table) {
            $table->timestamp('passed_at')->nullable()->after('last_seen_at');
        });
    }

    public function down(): void
    {
        Schema::table('auction_participants', function (Blueprint $table) {
            $table->dropColumn('passed_at');
        });
    }
};
