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
        Schema::table('break_settings', function (Blueprint $table) {
            $table->boolean('warning_enabled')->default(true)->after('skip_penalty');
            $table->unsignedInteger('warning_seconds')->default(60)->after('warning_enabled');
            // JSON array of active category keys e.g. ["physical","hydration","mental","movement"]
            $table->string('active_categories')->default('["physical","hydration","mental","movement"]')->after('warning_seconds');
        });
    }

    public function down(): void
    {
        Schema::table('break_settings', function (Blueprint $table) {
            $table->dropColumn(['warning_enabled', 'warning_seconds', 'active_categories']);
        });
    }
};
