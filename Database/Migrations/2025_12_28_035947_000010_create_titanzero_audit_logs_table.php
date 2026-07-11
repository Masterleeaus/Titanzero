<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('titanzero_audit_logs')) {
        Schema::create('titanzero_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('action', 80)->index(); // standards.search | assist.standards | library.upload | etc
            $table->string('route', 191)->nullable();
            $table->string('ip', 45)->nullable();
            $table->json('meta')->nullable(); // query, doc_ids, chunk_hashes, counts
            $table->timestamps();
        });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('titanzero_audit_logs');
    }
};
