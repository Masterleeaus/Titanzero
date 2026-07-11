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

        $columns = Schema::getColumnListing('menus');

        // We only operate when the expected columns exist.
        $required = ['key', 'label'];
        foreach ($required as $col) {
            if (!in_array($col, $columns, true)) {
                return;
            }
        }

        $hasRoute    = in_array('route', $columns, true);
        $hasParentId = in_array('parent_id', $columns, true);
        $hasType     = in_array('type', $columns, true);
        $hasOrder    = in_array('order', $columns, true);
        $hasActive   = in_array('is_active', $columns, true);

        // -----------------------------------------------------------------
        // Ensure the Titan Zero parent menu exists
        // -----------------------------------------------------------------
        $parentKey = 'titan_zero';

        $parent = DB::table('menus')->where('key', $parentKey)->first();

        if (!$parent) {
            $insert = [
                'key'   => $parentKey,
                'label' => 'Titan Zero',
            ];
            if ($hasRoute)  $insert['route'] = 'titan.zero.home';
            if ($hasType)   $insert['type'] = 'item';
            if ($hasOrder)  $insert['order'] = 900;
            if ($hasActive) $insert['is_active'] = 1;

            DB::table('menus')->insert($insert);
            $parent = DB::table('menus')->where('key', $parentKey)->first();
        } else {
            // Update route so clicking the parent opens Titan Zero home.
            if ($hasRoute) {
                DB::table('menus')->where('key', $parentKey)->update(['route' => 'titan.zero.home']);
            }
            if ($hasActive) {
                DB::table('menus')->where('key', $parentKey)->update(['is_active' => 1]);
            }
        }

        $parentId = $parent->id ?? null;

        // -----------------------------------------------------------------
        // Ensure child items exist and point to the correct routes
        // -----------------------------------------------------------------
        $children = [
            ['key' => 'titan_zero_generators', 'label' => 'Generators', 'route' => 'titan.zero.generators', 'order' => 901],
            ['key' => 'titan_zero_templates',  'label' => 'Templates',  'route' => 'titan.zero.templates',  'order' => 902],
        ];

        foreach ($children as $c) {
            $row = DB::table('menus')->where('key', $c['key'])->first();

            $payload = [
                'label' => $c['label'],
            ];
            if ($hasRoute)  $payload['route'] = $c['route'];
            if ($hasType)   $payload['type']  = 'item';
            if ($hasOrder)  $payload['order'] = $c['order'];
            if ($hasActive) $payload['is_active'] = 1;
            if ($hasParentId && $parentId) $payload['parent_id'] = $parentId;

            if (!$row) {
                $payload = array_merge(['key' => $c['key']], $payload);
                DB::table('menus')->insert($payload);
            } else {
                DB::table('menus')->where('key', $c['key'])->update($payload);
            }
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('menus')) {
            return;
        }

        DB::table('menus')->whereIn('key', [
            'titan_zero_generators',
            'titan_zero_templates',
        ])->delete();
    }
};
