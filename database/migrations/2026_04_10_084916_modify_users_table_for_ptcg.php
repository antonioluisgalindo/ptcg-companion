<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('surname')->nullable()->after('name');
            $table->string('player_id')->nullable()->unique()->after('surname')->comment('Pokemon Player ID from official system');
            $table->string('avatar')->nullable()->after('player_id');
            $table->text('bio')->nullable()->after('avatar');
            $table->boolean('is_active')->default(true)->after('bio');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['surname', 'player_id', 'avatar', 'bio', 'is_active']);
        });
    }
};
