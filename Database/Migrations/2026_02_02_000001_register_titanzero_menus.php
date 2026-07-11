<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('menus')) {
            return;
        }

        // Detect columns safely (Worksuite installs can differ)
        $cols = Schema::getColumnListing('menus');
        $has = fn(string $c) => in_array($c, $cols, true);

        if (!$has('key') || !$has('label')) {
            return;
        }

        // Find (or skip) the Titan group parent
        $parentId = null;
        if ($has('id')) {
            $parentRow = DB::table('menus')
                ->where('key', 'titan')
                ->first();
            $parentId = $parentRow->id ?? null;
        }

        $now = now();
        $payload = [
            'key'   => 'titan_zero',
            'label' => 'Titan Zero',
        ];

        if ($has('route')) {
            $payload['route'] = 'titan.zero.home';
        }
        if ($has('type')) {
            $payload['type'] = 'item';
        }
        if ($has('order')) {
            $payload['order'] = 10;
        }
        if ($has('is_active')) {
            $payload['is_active'] = 1;
        }
        if ($has('icon')) {
            $payload['icon'] = 'ti ti-sparkles';
        }
        if ($has('parent_id') && $parentId) {
            $payload['parent_id'] = $parentId;
        }
        if ($has('created_at')) {
            $payload['created_at'] = $now;
        }
        if ($has('updated_at')) {
            $payload['updated_at'] = $now;
        }

        DB::table('menus')->updateOrInsert(
            ['key' => 'titan_zero'],
            $payload
        );
    }

    public function down(): void
    {
        if (!Schema::hasTable('menus')) {
            return;
        }
        if (!Schema::hasColumn('menus', 'key')) {
            return;
        }
        DB::table('menus')->where('key', 'titan_zero')->delete();
    }
};
