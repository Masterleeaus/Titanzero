<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('titanzero_coaches')) {
        Schema::create('titanzero_coaches', function (Blueprint $table) {
            $table->id();
            $table->string('key', 64)->unique();
            $table->string('name', 120);
            $table->text('description')->nullable();
            $table->json('rules')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
        });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('titanzero_coaches');
    }
};
