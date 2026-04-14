<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained()->onDelete('cascade');
            $table->integer('number');
            $table->enum('type', ['swiss', 'top_cut'])->default('swiss');
            $table->enum('status', ['pending', 'active', 'finished'])->default('pending');
            $table->dateTime('started_at')->nullable();
            $table->dateTime('finished_at')->nullable();
            $table->dateTime('time_limit_at')->nullable()->comment('When time is called');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rounds');
    }
};
