<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('titanzero_documents')) {

        Schema::table('titanzero_documents', function (Blueprint $table) {
            if (!Schema::hasColumn('titanzero_documents', 'doc_type')) {
                $table->string('doc_type', 40)->nullable()->index();
            }
            if (!Schema::hasColumn('titanzero_documents', 'authority_level')) {
                $table->string('authority_level', 40)->nullable()->index();
            }
            if (!Schema::hasColumn('titanzero_documents', 'jurisdiction')) {
                $table->string('jurisdiction', 40)->nullable()->index();
            }
            if (!Schema::hasColumn('titanzero_documents', 'is_superseded')) {
                $table->boolean('is_superseded')->default(false)->index();
            }
            if (!Schema::hasColumn('titanzero_documents', 'preferred_weight')) {
                $table->unsignedSmallInteger('preferred_weight')->default(0)->index();
            }
            if (!Schema::hasColumn('titanzero_documents', 'coach_override')) {
                $table->string('coach_override', 64)->nullable()->index();
            }
            if (!Schema::hasColumn('titanzero_documents', 'meta')) {
                $table->json('meta')->nullable();
            }
        });

        }
    }

    public function down(): void
    {
        // safe down omitted
    }
};
