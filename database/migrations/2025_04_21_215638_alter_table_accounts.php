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
        Schema::table('accounts', function (Blueprint $table) {
            $table->enum('region', ['NA1', 'BR1', 'LA1', 'LA2', 'KR', 'JP1', 'EUN1', 'EUW1', 'ME1', 'TR1', 'RU', 'OC1', 'SG2', 'TW2', 'VN2']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn('region');
        });
    }
};
