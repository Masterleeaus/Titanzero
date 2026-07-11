<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('titanzero_document_tag')) {
        Schema::create('titanzero_document_tag', function (Blueprint $table) {
            $table->unsignedBigInteger('document_id');
            $table->unsignedBigInteger('tag_id');
            $table->primary(['document_id','tag_id']);
            $table->index('document_id');
            $table->index('tag_id');
        });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('titanzero_document_tag');
    }
};
