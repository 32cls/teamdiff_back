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
        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->integer("duration")->nullable(false);
            $table->timestamp("game_creation")->nullable(false);
        });
        Schema::create('participants', function (Blueprint $table) {
            $table->id();
            $table->integer("champion_id")->nullable(false);
            $table->integer("team_id")->nullable(false);
            $table->string("team_position")->nullable(false);
            $table->boolean("win")->nullable(false);
            $table->integer("kills")->nullable(false);
            $table->integer("deaths")->nullable(false);
            $table->integer("assists")->nullable(false);
            $table->integer("level")->nullable(false);
            $table->foreignId("summoner_id")->constrained(table: 'summoners', column: 'id');
            $table->foreignId("match_id")->constrained(table: 'matches', column: 'id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('participants');
        Schema::dropIfExists('matches');
    }
};
