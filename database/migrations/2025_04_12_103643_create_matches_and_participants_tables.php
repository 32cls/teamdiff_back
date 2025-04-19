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
        Schema::create('lolmatches', function (Blueprint $table) {
            $table->string('id')->primary();
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
            $table->string('match_id')->nullable(false);
            $table->string('summoner_id')->nullable(false);
            $table->foreign("summoner_id")->references('id')->on('summoners')->onDelete('cascade');
            $table->foreign("match_id")->references('id')->on('lolmatches')->onDelete('cascade');
            $table->unique(['match_id', 'summoner_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('participants');
        Schema::dropIfExists('lolmatches');
    }
};
