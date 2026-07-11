<?php

namespace Modules\TitanZero\Canvas\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * CanvasWorkflow — persists a visual workflow definition created in the Canvas UI.
 * Workflows are tenant-scoped (company_id required).
 */
class CanvasWorkflow extends Model
{
    protected $table = 'titanzero_canvas_workflows';

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'definition',   // JSON: nodes + edges from the canvas editor
        'trigger_type', // manual | signal | schedule
        'trigger_config',
        'status',       // draft | active | archived
        'created_by',
    ];

    protected $casts = [
        'definition'     => 'array',
        'trigger_config' => 'array',
    ];

    public function runs(): HasMany
    {
        return $this->hasMany(CanvasWorkflowRun::class, 'workflow_id');
    }
}
