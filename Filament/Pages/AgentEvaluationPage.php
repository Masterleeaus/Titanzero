<?php

namespace Modules\TitanZero\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

/**
 * AgentEvaluationPage — Filament dashboard for agent evaluation scores.
 *
 * Displays the latest evaluation scores per agent per company.
 * Accessible from the TitanZero Filament panel.
 */
class AgentEvaluationPage extends Page
{
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static \UnitEnum|string|null $navigationGroup = 'Titan Zero';

    protected static ?string $navigationLabel = 'Agent Evaluation';

    protected static ?string $title = 'Agent Evaluation Dashboard';

    protected static ?int $navigationSort = 10;

    protected static ?string $slug = 'agent-evaluation';

    protected string $view = 'filament.pages.suite-placeholder';

    protected function getViewData(): array
    {
        $recentScores = $this->loadRecentScores();

        return [
            'emoji'        => '📊',
            'module'       => 'Agent Evaluation Dashboard',
            'description'  => 'Weekly evaluation scores for all active Titan Zero agents. Tracks task completion, hallucination flags, tool accuracy, and composite quality scores.',
            'capabilities' => [
                'Task completion rate per agent',
                'Hallucination detection flag',
                'Tool accuracy scoring',
                'Response latency tracking',
                'Composite quality score (weighted)',
            ],
            'scores' => $recentScores,
        ];
    }

    private function loadRecentScores(): array
    {
        if (!DB::getSchemaBuilder()->hasTable('titanzero_agent_evaluation_scores')) {
            return [];
        }

        return DB::table('titanzero_agent_evaluation_scores')
            ->select([
                'agent_name',
                'company_id',
                DB::raw('AVG(task_completion_rate) as avg_task_completion'),
                DB::raw('AVG(hallucination_flag) as avg_hallucination'),
                DB::raw('AVG(tool_accuracy) as avg_tool_accuracy'),
                DB::raw('AVG(composite_score) as avg_composite'),
                DB::raw('COUNT(*) as total_evals'),
            ])
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('agent_name', 'company_id')
            ->orderByDesc('avg_composite')
            ->get()
            ->toArray();
    }
}
