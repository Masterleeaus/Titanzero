<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('titanzero_document_tags')) {
        Schema::create('titanzero_document_tags', function (Blueprint $table) {
            $table->id();
            $table->string('key', 80)->unique();
            $table->string('name', 120);
            $table->string('group', 60)->nullable();
            $table->timestamps();
        });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('titanzero_document_tags');
    }
};
