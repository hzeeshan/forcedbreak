<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('break_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('interval_minutes')->default(25);
            $table->boolean('auto_launch')->default(false);
            $table->boolean('skip_penalty')->default(true);
            $table->timestamps();
        });

        DB::table('break_settings')->insert([
            'interval_minutes' => 25,
            'auto_launch' => false,
            'skip_penalty' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('break_settings');
    }
};
