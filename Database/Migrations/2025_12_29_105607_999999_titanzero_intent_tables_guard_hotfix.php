<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Hotfix: ensure table-exists crashes never occur during Worksuite migration status checks.
        // No-op if tables already exist.
        if (Schema::hasTable('titanzero_intent_runs')) {
            return;
        }
    }

    public function down(): void {}
};
