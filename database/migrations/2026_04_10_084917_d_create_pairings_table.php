<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pairings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('round_id')->constrained()->onDelete('cascade');
            $table->foreignId('player1_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('player2_id')->nullable()->constrained('users')->onDelete('cascade')->comment('Null = bye');
            $table->integer('table_number')->nullable();
            $table->enum('result', ['pending', 'player1_win', 'player2_win', 'draw', 'bye'])->default('pending');
            $table->boolean('result_confirmed')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pairings');
    }
};
