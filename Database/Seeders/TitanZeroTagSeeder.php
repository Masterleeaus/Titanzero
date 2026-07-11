<?php

namespace Modules\TitanZero\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\TitanZero\Entities\TitanZeroDocumentTag;

class TitanZeroTagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            ['key'=>'business','name'=>'Business & Operations','group'=>'domain'],
            ['key'=>'finance','name'=>'Finance & Cashflow','group'=>'domain'],
            ['key'=>'pricing','name'=>'Pricing & Margin','group'=>'domain'],
            ['key'=>'contracts','name'=>'Contracts','group'=>'domain'],
            ['key'=>'disputes','name'=>'Disputes','group'=>'domain'],
            ['key'=>'standards','name'=>'Standards / NCC / AS-NZS','group'=>'domain'],
            ['key'=>'compliance','name'=>'Compliance','group'=>'domain'],
            ['key'=>'safety','name'=>'WHS / Safety','group'=>'domain'],
            ['key'=>'site','name'=>'Site Execution','group'=>'domain'],
            ['key'=>'foreman','name'=>'Foreman / Supervisor','group'=>'role'],
            ['key'=>'estimation','name'=>'Estimation','group'=>'domain'],
        ];

        foreach ($tags as $t) {
            TitanZeroDocumentTag::query()->updateOrCreate(['key'=>$t['key']], $t);
        }
    }
}
