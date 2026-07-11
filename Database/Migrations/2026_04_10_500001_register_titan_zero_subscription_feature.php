<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        // Register titanzero in the modules table so it appears in SA modules list
        if (Schema::hasTable('modules')) {
            $exists = DB::table('modules')->where('module_name', 'titanzero')->exists();
            if (!$exists) {
                DB::table('modules')->insertOrIgnore([
                    'module_name' => 'titanzero',
                    'description' => 'TitanZero — zero-config AI integration, smart scheduling suggestions and intelligent automation',
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            } else {
                DB::table('modules')->where('module_name', 'titanzero')->update([
                    'description' => 'TitanZero — zero-config AI integration, smart scheduling suggestions and intelligent automation',
                    'updated_at'  => now(),
                ]);
            }
        }

        // Register titan_zero as a feature in subscription_package_features
        if (!Schema::hasTable('subscription_package_features')) {
            return;
        }

        if (!Schema::hasTable('subscription_packages')) {
            return;
        }

        $cols = Schema::getColumnListing('subscription_package_features');
        $hasFeature = in_array('feature', $cols, true);
        $hasPkgId   = in_array('subscription_package_id', $cols, true);
        $hasId      = in_array('id', $cols, true);

        if (!$hasFeature || !$hasPkgId) {
            return;
        }

        $packages = DB::table('subscription_packages')->get();

        foreach ($packages as $package) {
            $existing = DB::table('subscription_package_features')
                ->where('subscription_package_id', $package->id)
                ->where('feature', 'titan_zero')
                ->exists();

            if (!$existing) {
                $row = [
                    'feature'                 => 'titan_zero',
                    'subscription_package_id' => $package->id,
                    'created_at'              => now(),
                    'updated_at'              => now(),
                ];

                if ($hasId) {
                    $row['id'] = Str::uuid()->toString();
                }

                DB::table('subscription_package_features')->insert($row);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('subscription_package_features')) {
            DB::table('subscription_package_features')
                ->where('feature', 'titan_zero')
                ->delete();
        }

        if (Schema::hasTable('modules')) {
            DB::table('modules')->where('module_name', 'titanzero')->delete();
        }
    }
};
