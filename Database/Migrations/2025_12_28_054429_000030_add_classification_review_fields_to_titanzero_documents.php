<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('titanzero_documents')) {

        Schema::table('titanzero_documents', function (Blueprint $table) {
            if (!Schema::hasColumn('titanzero_documents', 'classification_confidence')) {
                $table->unsignedSmallInteger('classification_confidence')->default(0)->index(); // 0..100
            }
            if (!Schema::hasColumn('titanzero_documents', 'classification_source')) {
                $table->string('classification_source', 40)->nullable()->index(); // heuristic_v1|toc_v2|manual
            }
            if (!Schema::hasColumn('titanzero_documents', 'review_status')) {
                $table->string('review_status', 30)->default('pending')->index(); // pending|approved|needs_work
            }
            if (!Schema::hasColumn('titanzero_documents', 'reviewed_by')) {
                $table->unsignedBigInteger('reviewed_by')->nullable()->index();
            }
            if (!Schema::hasColumn('titanzero_documents', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->index();
            }
        });

        }
    }

    public function down(): void
    {
        // safe down omitted
    }
};
