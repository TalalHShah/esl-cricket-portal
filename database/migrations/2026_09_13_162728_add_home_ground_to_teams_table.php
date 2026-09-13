<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->string('home_ground_name')->nullable()->after('description');
            $table->string('home_ground_location')->nullable()->after('home_ground_name');
            $table->string('home_ground_image')->nullable()->after('home_ground_location');
        });
    }

    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn(['home_ground_name', 'home_ground_location', 'home_ground_image']);
        });
    }
};
