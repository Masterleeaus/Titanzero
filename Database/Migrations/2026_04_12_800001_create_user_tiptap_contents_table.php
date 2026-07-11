<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('user_tiptap_contents')) {
        Schema::create('user_tiptap_contents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('save_contentable_id');
            $table->string('save_contentable_type');
            $table->string('title')->nullable();
            $table->text('input')->nullable();
            $table->text('output')->nullable();
            $table->timestamps();

            $table->index(['save_contentable_id', 'save_contentable_type'], 'tiptap_contentable_index');
        });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_tiptap_contents');
    }
};
