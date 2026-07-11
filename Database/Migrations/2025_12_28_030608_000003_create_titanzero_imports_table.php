<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('titanzero_imports')) {
        Schema::create('titanzero_imports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_id')->nullable()->index();
            $table->string('status', 30)->default('pending'); // pending|processing|done|failed
            $table->text('message')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('titanzero_imports');
    }
};
