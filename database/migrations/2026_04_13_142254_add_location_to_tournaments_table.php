<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tournaments', function (Blueprint $table) {
            $table->foreignId('province_id')->nullable()->after('city')->constrained()->onDelete('set null');
            $table->foreignId('locality_id')->nullable()->after('province_id')->constrained()->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('tournaments', function (Blueprint $table) {
            $table->dropForeign(['locality_id']);
            $table->dropForeign(['province_id']);
            $table->dropColumn(['locality_id', 'province_id']);
        });
    }
};
