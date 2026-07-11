<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('titanzero_job_access_notes')) {
        Schema::create('titanzero_job_access_notes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->unsignedBigInteger('job_id')->index();          // fsm_orders.id
            $table->string('field_name', 64)->index();              // access_code | alarm_instructions | key_safe | general_notes
            $table->text('ciphertext');                             // base64-encoded AES-GCM ciphertext (plaintext never stored)
            $table->string('iv_b64', 64);                           // base64-encoded 96-bit IV for AES-GCM
            $table->unsignedBigInteger('assigned_user_id')->nullable()->index(); // only this user can decrypt
            $table->unsignedTinyInteger('version')->default(1);     // incremented on re-encryption
            $table->timestamps();

            $table->unique(['job_id', 'field_name']);
        });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('titanzero_job_access_notes');
    }
};
