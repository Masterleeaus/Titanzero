<?php

namespace Modules\TitanZero\Entities;

use Illuminate\Database\Eloquent\Model;

class TitanZeroIntentRun extends Model
{
    protected $table = 'titanzero_intent_runs';

    protected $fillable = [
        'intent','confidence','risk_level','execution_mode',
        'entities_json','missing_entities_json','page_context_json',
        'result_json','status','user_id'
    ];
}
