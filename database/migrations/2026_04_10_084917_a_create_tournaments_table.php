<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tournaments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('organizer_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['draft', 'registration', 'ongoing', 'finished', 'cancelled'])->default('draft');
            $table->enum('format', ['standard', 'expanded', 'unlimited'])->default('standard');
            $table->integer('max_players')->default(32);
            $table->integer('swiss_rounds')->nullable()->comment('Auto-calculated if null');
            $table->boolean('top_cut_enabled')->default(false);
            $table->enum('top_cut_size', ['4', '8', '16', '32'])->nullable();
            $table->integer('match_time_minutes')->default(50);
            $table->dateTime('registration_opens_at')->nullable();
            $table->dateTime('registration_closes_at')->nullable();
            $table->dateTime('starts_at')->nullable();
            $table->string('venue')->nullable();
            $table->string('city')->nullable();
            $table->boolean('is_public')->default(true);
            $table->boolean('require_deck_list')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tournaments');
    }
};
