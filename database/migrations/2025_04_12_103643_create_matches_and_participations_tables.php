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
            $table->timestamp("gameCreation")->nullable(false);
        });
        Schema::create('participations', function (Blueprint $table) {
            $table->id();
            $table->string("championId")->nullable(false);
            $table->integer("teamId")->nullable(false);
            $table->string("role")->nullable(false);
            $table->boolean("win")->nullable(false);
            $table->integer("kills")->nullable(false);
            $table->integer("deaths")->nullable(false);
            $table->integer("assists")->nullable(false);
            $table->integer("level")->nullable(false);
            $table->string('matchId')->nullable(false);
            $table->string('summonerId')->nullable(false);
            $table->foreign("summonerId")->references('id')->on('summoners')->onDelete('cascade');
            $table->foreign("matchId")->references('id')->on('lolmatches')->onDelete('cascade');
            $table->unique(['matchId', 'summonerId']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('participations');
        Schema::dropIfExists('lolmatches');
    }
};
