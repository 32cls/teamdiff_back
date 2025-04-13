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
            $table->id();
            $table->string('content');
            $table->float('rating');
            $table->string('match_id');
            $table->foreignId('author_id')->constrained(table: 'summoners', column: 'id');
            $table->foreignId('reviewed_id')->constrained(table: 'summoners', column: 'id');
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
