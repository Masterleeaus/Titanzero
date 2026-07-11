<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('titanzero_job_access_audit')) {
        Schema::create('titanzero_job_access_audit', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->unsignedBigInteger('job_id')->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('action', 32)->index(); // view | encrypt | reencrypt | decrypt_request
            $table->string('field_name', 64)->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamps();
        });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('titanzero_job_access_audit');
    }
};
