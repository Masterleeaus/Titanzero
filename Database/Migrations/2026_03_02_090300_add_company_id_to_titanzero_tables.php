<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('assistant_templates') && !Schema::hasColumn('assistant_templates', 'company_id')) {
            Schema::table('assistant_templates', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }
        if (Schema::hasTable('titan_assist_usages') && !Schema::hasColumn('titan_assist_usages', 'company_id')) {
            Schema::table('titan_assist_usages', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }
        if (Schema::hasTable('titanzero_artifacts') && !Schema::hasColumn('titanzero_artifacts', 'company_id')) {
            Schema::table('titanzero_artifacts', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }
        if (Schema::hasTable('titanzero_audit_logs') && !Schema::hasColumn('titanzero_audit_logs', 'company_id')) {
            Schema::table('titanzero_audit_logs', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }
        if (Schema::hasTable('titanzero_coaches') && !Schema::hasColumn('titanzero_coaches', 'company_id')) {
            Schema::table('titanzero_coaches', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }
        if (Schema::hasTable('titanzero_document_chunks') && !Schema::hasColumn('titanzero_document_chunks', 'company_id')) {
            Schema::table('titanzero_document_chunks', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }
        if (Schema::hasTable('titanzero_document_tag') && !Schema::hasColumn('titanzero_document_tag', 'company_id')) {
            Schema::table('titanzero_document_tag', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }
        if (Schema::hasTable('titanzero_document_tags') && !Schema::hasColumn('titanzero_document_tags', 'company_id')) {
            Schema::table('titanzero_document_tags', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }
        if (Schema::hasTable('titanzero_documents') && !Schema::hasColumn('titanzero_documents', 'company_id')) {
            Schema::table('titanzero_documents', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }
        if (Schema::hasTable('titanzero_imports') && !Schema::hasColumn('titanzero_imports', 'company_id')) {
            Schema::table('titanzero_imports', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }
        if (Schema::hasTable('titanzero_intent_runs') && !Schema::hasColumn('titanzero_intent_runs', 'company_id')) {
            Schema::table('titanzero_intent_runs', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }
    }

    public function down(): void
    {
        // intentionally non-destructive
    }
};
