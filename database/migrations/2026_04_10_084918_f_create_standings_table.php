<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('standings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('match_points')->default(0);
            $table->integer('matches_played')->default(0);
            $table->integer('matches_won')->default(0);
            $table->integer('matches_lost')->default(0);
            $table->integer('matches_drawn')->default(0);
            $table->integer('games_won')->default(0);
            $table->integer('games_lost')->default(0);
            $table->integer('games_played')->default(0);
            $table->decimal('opponent_win_pct', 8, 4)->default(0)->comment('OWP tiebreaker');
            $table->decimal('opp_opp_win_pct', 8, 4)->default(0)->comment('OOWP tiebreaker');
            $table->decimal('game_win_pct', 8, 4)->default(0)->comment('GWP tiebreaker');
            $table->integer('position')->nullable();
            $table->integer('byes_received')->default(0);
            $table->timestamps();

            $table->unique(['tournament_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('standings');
    }
};
