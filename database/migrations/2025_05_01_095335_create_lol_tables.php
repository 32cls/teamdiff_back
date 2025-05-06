<?php

declare(strict_types=1);

use App\Enums\RegionEnum;
use App\Models\Game;
use App\Models\Player;
use App\Models\Summoner;
use App\Models\User;
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
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->enum('region', RegionEnum::valueArray());
            $table->string('icon_id');
            $table->integer('level');
            $table->timestamps();

        });

        Schema::create('lol_games', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('riot_match_id')->unique();
            $table->integer('duration');
            $table->string('winning_riot_team_id');
            $table->timestamp('started_at');
            $table->timestamps();
        });

        Schema::create('lol_players', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignIdFor(Game::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Summoner::class);
            $table->string('riot_champion_name');
            $table->string('riot_team_id');
            $table->string('riot_role');
            $table->integer('kills');
            $table->integer('deaths');
            $table->integer('assists');
            $table->integer('level');
            $table->timestamps();

            $table->unique(['summoner_id', 'game_id']);
        });

        Schema::create('lol_reviews', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignIdFor(Player::class, 'author_player_id')->constrained();
            $table->foreignIdFor(Player::class, 'subject_player_id')->constrained();
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
