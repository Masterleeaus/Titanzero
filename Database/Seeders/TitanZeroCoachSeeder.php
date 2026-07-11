<?php

namespace Modules\TitanZero\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\TitanZero\Entities\TitanZeroCoach;

class TitanZeroCoachSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            [
                'key' => 'business',
                'name' => 'Business Coach',
                'description' => 'Owner/operator coaching: pricing, cashflow, ops, growth.',
                'rules' => ['retrieval_filters' => ['include_tags' => ['business','finance','pricing','contracts'], 'exclude_tags' => ['standards'], 'exclude_superseded' => true]],
            ],
            [
                'key' => 'compliance',
                'name' => 'Compliance Coach',
                'description' => 'Standards-first guidance with citations and audit trail.',
                'rules' => ['retrieval_filters' => ['include_tags' => ['standards','compliance','safety'], 'exclude_superseded' => true]],
            ],
            [
                'key' => 'foreman',
                'name' => 'Foreman Coach',
                'description' => 'Site execution: prestart, sequencing, toolbox talks, daily plan.',
                'rules' => ['retrieval_filters' => ['include_tags' => ['site','foreman','safety'], 'exclude_superseded' => true]],
            ],
            [
                'key' => 'estimator',
                'name' => 'Estimator Coach',
                'description' => 'Estimating, costing, allowances, margin and risk.',
                'rules' => ['retrieval_filters' => ['include_tags' => ['pricing','estimation','business'], 'exclude_tags' => ['standards'], 'exclude_superseded' => true]],
            ],
            [
                'key' => 'contracts',
                'name' => 'Contracts & Disputes Coach',
                'description' => 'Variations, payment issues, customer comms and dispute playbooks.',
                'rules' => ['retrieval_filters' => ['include_tags' => ['contracts','disputes','business'], 'exclude_superseded' => true]],
            ],
        ];

        foreach ($defaults as $d) {
            TitanZeroCoach::query()->updateOrCreate(['key'=>$d['key']], $d);
        }
    }
}
