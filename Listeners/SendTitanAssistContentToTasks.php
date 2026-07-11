<?php

namespace Modules\TitanZero\Listeners;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\TitanZero\Events\TitanZeroContentGenerated;

class SendTitanZeroContentToTasks
{
    public function handle(TitanZeroContentGenerated $event): void
    {
        if (! Config::get('aiassistant.integrations.tasks_enabled', true)) {
            return;
        }

        $content = trim($event->content ?? '');
        if ($content === '') {
            return;
        }

        $userId    = $event->userId;
        $companyId = $event->companyId;

        try {
            $heading = (string) Str::limit(preg_replace('/\s+/', ' ', strip_tags($content)), 80);

            if ($heading === '') {
                $heading = 'Titan Zero task';
            }

            $boardColumnId = DB::table('taskboard_columns')->min('id') ?? 1;

            DB::table('tasks')->insert([
                'company_id'       => $companyId,
                'heading'          => $heading,
                'description'      => $content,
                'due_date'         => null,
                'start_date'       => now(),
                'project_id'       => null,
                'task_category_id' => null,
                'priority'         => 'medium',
                'status'           => 'incomplete',
                'board_column_id'  => $boardColumnId,
                'column_priority'  => 0,
                'estimate_hours'   => 0,
                'estimate_minutes' => 0,
                'created_by'       => $userId,
                'added_by'         => $userId,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Titan Zero: failed to send content to Tasks', [
                'error'     => $e->getMessage(),
                'user_id'   => $userId,
                'company_id'=> $companyId,
            ]);
        }
    }
}
