<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('match_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pairing_id')->constrained()->onDelete('cascade');
            $table->foreignId('reported_by')->constrained('users')->onDelete('cascade');
            // Overall match result
            $table->enum('match_result', ['player1_win', 'player2_win', 'draw'])->nullable();
            // Individual game results (Best of 3)
            $table->integer('player1_wins')->default(0)->comment('Games won by player 1');
            $table->integer('player2_wins')->default(0)->comment('Games won by player 2');
            $table->integer('ties')->default(0)->comment('Games that ended in tie');
            // Notes
            $table->text('notes')->nullable()->comment('Judge notes or dispute resolution');
            $table->boolean('is_judge_entry')->default(false)->comment('True if entered/edited by judge');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('match_results');
    }
};
