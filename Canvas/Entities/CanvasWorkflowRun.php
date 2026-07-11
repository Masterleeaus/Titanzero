<?php

namespace Modules\TitanZero\Canvas\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * CanvasWorkflowRun — execution record for a single Canvas workflow run.
 * Immutable after creation; status updates written to existing columns.
 */
class CanvasWorkflowRun extends Model
{
    protected $table = 'titanzero_canvas_workflow_runs';

    protected $fillable = [
        'company_id',
        'workflow_id',
        'triggered_by', // user_id or 'system'
        'input',        // JSON payload that triggered the run
        'output',       // JSON result
        'status',       // running | completed | failed
        'error_message',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'input'        => 'array',
        'output'       => 'array',
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(CanvasWorkflow::class, 'workflow_id');
    }
}
