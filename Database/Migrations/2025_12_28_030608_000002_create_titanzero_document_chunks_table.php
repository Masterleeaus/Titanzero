<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('titanzero_document_chunks')) {
        Schema::create('titanzero_document_chunks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_id')->index();
            $table->unsignedInteger('chunk_index')->default(0);
            $table->longText('content');
            $table->string('content_hash', 64)->index();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['document_id','chunk_index'], 'tz_doc_chunk_unique');
            $table->foreign('document_id')->references('id')->on('titanzero_documents')->onDelete('cascade');
        });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('titanzero_document_chunks');
    }
};
