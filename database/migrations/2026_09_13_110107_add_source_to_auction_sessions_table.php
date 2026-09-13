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
        Schema::table('auction_sessions', function (Blueprint $table) {
            $table->enum('source', ['admin', 'draft', 'market'])->default('admin')->after('auction_draft_id');
        });

        DB::table('auction_sessions')->whereNotNull('auction_draft_id')->update(['source' => 'draft']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('auction_sessions', function (Blueprint $table) {
            $table->dropColumn('source');
        });
    }
};
