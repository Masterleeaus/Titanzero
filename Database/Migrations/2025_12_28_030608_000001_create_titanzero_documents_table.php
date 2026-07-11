<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('titanzero_documents')) {
        Schema::create('titanzero_documents', function (Blueprint $table) {
            $table->id();
            $table->string('title', 191);
            $table->string('source', 50)->default('upload');
            $table->string('storage_path', 255)->nullable();
            $table->string('sha256', 64)->nullable()->index();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('titanzero_documents');
    }
};
