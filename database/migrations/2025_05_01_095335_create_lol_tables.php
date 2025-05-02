<?php

declare(strict_types=1);

use App\Enums\RegionEnum;
use App\Enums\RoleEnum;
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
        Schema::create('lol_summoners', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('riot_summoner_id')->unique();
            $table->string('user_puuid');
            $table->enum('region', RegionEnum::valueArray());
            $table->string('icon_id');
            $table->integer('level');
            $table->timestamps();

            $table->foreign('user_puuid')->references('riot_puuid')->on('users')->onDelete('cascade');
        });

        Schema::create('lol_games', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('riot_match_id')->unique();
            $table->integer('duration');
            $table->timestamp('started_at');
            $table->timestamps();

        });

        Schema::create('lol_players', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('riot_match_id');
            $table->string('riot_summoner_id');
            $table->string('champion_internal_name');
            $table->integer('team_id');
            $table->enum('role', RoleEnum::valueArray());
            $table->boolean('has_won');
            $table->integer('kills');
            $table->integer('deaths');
            $table->integer('assists');
            $table->integer('level');
            $table->timestamps();

            $table->foreign('riot_match_id')->references('riot_match_id')->on('lol_games')->onDelete('cascade');
            $table->unique(['riot_match_id', 'riot_summoner_id']);
        });

        Schema::create('lol_reviews', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('author_id')->constrained('lol_players', 'id');
            $table->foreignUlid('subject_id')->constrained('lol_players', 'id');
            $table->text('content');
            $table->tinyInteger('rating');

            $table->softDeletes();
            $table->timestamps();
        });

        DB::statement(/** @lang PostgreSQL */ <<<'PGSQL'
            ALTER TABLE lol_reviews
                ADD CONSTRAINT rating_amount_between
                CHECK ( rating > 0 AND rating <= 5 );
        PGSQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lol_reviews');
        Schema::dropIfExists('lol_players');
        Schema::dropIfExists('lol_games');
        Schema::dropIfExists('lol_summoners');
    }
};
