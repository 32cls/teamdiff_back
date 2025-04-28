<?php

declare(strict_types=1);

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
            $table->integer('duration');
            $table->timestamp('game_creation');
        });

        Schema::create('participations', function (Blueprint $table) {
            $table->id();
            $table->string('champion_id');
            $table->integer('team_id');
            $table->string('role');
            $table->boolean('win');
            $table->integer('kills');
            $table->integer('deaths');
            $table->integer('assists');
            $table->integer('level');
            $table->string('match_id');
            $table->string('summoner_id');
            $table->foreign('summoner_id')->references('id')->on('summoners')->onDelete('cascade');
            $table->foreign('match_id')->references('id')->on('lolmatches')->onDelete('cascade');
            $table->unique(['match_id', 'summoner_id']);
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
