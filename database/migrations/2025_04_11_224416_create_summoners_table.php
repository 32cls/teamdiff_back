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
        Schema::create('summoners', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->integer("icon")->nullable();
            $table->timestamp("revision_date")->nullable();
            $table->integer("level")->nullable();
            $table->string("account_id")->nullable();
            $table->foreign("account_id")->references('puuid')->on('accounts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('summoners');
    }
};
