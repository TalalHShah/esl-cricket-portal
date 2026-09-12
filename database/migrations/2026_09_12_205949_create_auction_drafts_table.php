<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A live "country draft" event: managers take turns nominating
     * players country-by-country (a spun/random country, then every
     * team in rotation either nominates a free agent from it — which
     * opens a mini-auction at that player's base value — or skips;
     * a country "burns" once a full lap passes with no nominations).
     * Individual player bidding still lives on AuctionSession; this
     * table only tracks the whose-turn/what-country orchestration
     * around it.
     */
    public function up(): void
    {
        Schema::create('auction_drafts', function (Blueprint $table) {
            $table->id();
            $table->enum('status', ['setup', 'active', 'completed'])->default('setup');
            $table->json('turn_order');
            $table->unsignedTinyInteger('country_picker_index')->default(0);
            $table->unsignedTinyInteger('active_picker_index')->nullable();
            $table->string('current_country')->nullable();
            $table->unsignedTinyInteger('consecutive_skips')->default(0);
            $table->json('burned_countries')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auction_drafts');
    }
};
