<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tournament_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('deck_name')->nullable();
            $table->text('deck_list')->nullable()->comment('Deck list in text format');
            $table->enum('status', ['pending', 'confirmed', 'dropped', 'disqualified'])->default('pending');
            $table->integer('seed')->nullable()->comment('Seeding for first round');
            $table->timestamp('dropped_at')->nullable();
            $table->timestamps();

            $table->unique(['tournament_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tournament_registrations');
    }
};
