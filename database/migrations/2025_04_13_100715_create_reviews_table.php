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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id()->primary();
            $table->string('content')->nullable(false);
            $table->float('rating')->nullable(false);
            $table->timestamps();
            $table->string('match_id')->nullable(false);
            $table->string('author_id')->nullable(false);
            $table->string('reviewee_id')->nullable(false);
            $table->foreign('author_id')->references('id')->on('summoners')->onDelete('cascade');
            $table->foreign('reviewee_id')->references('id')->on('summoners')->onDelete('cascade');
            $table->foreign("match_id")->references('id')->on('lolmatches')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
