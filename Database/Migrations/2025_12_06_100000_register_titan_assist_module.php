<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('modules')) {
            DB::table('modules')->updateOrInsert(
                ['module_name' => 'Titan Zero'],
                [
                    'description' => 'Titan Zero – Titan Zero powered by Titan Core',
                    'status'      => 1,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]
            );
        }

        if (Schema::hasTable('module_settings')) {
            DB::table('module_settings')->updateOrInsert(
                ['module_name' => 'Titan Zero'],
                [
                    'status'     => 'active',
                    'type'       => 'addon',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // Optional: attach Titan Zero alias to selected packages if the column exists.
        if (Schema::hasTable('packages')) {
            try {
                $packages = DB::table('packages')->get();
                foreach ($packages as $pkg) {
                    $features = json_decode($pkg->module_in_package ?? '[]', true) ?: [];
                    if (!in_array('aiassistant', $features, true)) {
                        // Example rule: expose Titan Zero on higher plans only.
                        if (in_array($pkg->name, ['Professional', 'Enterprise'])) {
                            $features[] = 'aiassistant';
                            DB::table('packages')
                                ->where('id', $pkg->id)
                                ->update(['module_in_package' => json_encode($features)]);
                        }
                    }
                }
            } catch (\Throwable $e) {
                // In case the column is missing or structure differs, just skip silently.
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('module_settings')) {
            DB::table('module_settings')
                ->where('module_name', 'Titan Zero')
                ->delete();
        }

        if (Schema::hasTable('modules')) {
            DB::table('modules')
                ->where('module_name', 'Titan Zero')
                ->delete();
        }
    }
};
