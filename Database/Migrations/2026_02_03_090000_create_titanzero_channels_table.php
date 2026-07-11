<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('titanzero_channels')) {
            return;
        }

        Schema::create('titanzero_channels', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();

            $table->string('key', 50)->index();     // web, sms, whatsapp, email, voice
            $table->string('label', 100)->nullable();
            $table->boolean('enabled')->default(true);

            $table->json('config')->nullable();     // channel-specific config
            $table->json('health')->nullable();     // computed health snapshot
            $table->timestamp('last_checked_at')->nullable();

            $table->timestamps();

            $table->unique(['company_id','key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('titanzero_channels');
    }
};
