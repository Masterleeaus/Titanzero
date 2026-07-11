<?php

return [
    // Intent confidence thresholds
    'intent' => [
        'auto_execute_min' => 90,   // >= 90 => can auto-execute low-risk actions (once wired)
        'confirm_min'      => 70,   // 70..89 => require confirmation
        'clarify_below'    => 70,   // < 70 => ask clarifying questions
    ],

    // Risk levels
    'risk' => [
        'low' => ['explain_page', 'help_fill_form', 'find_setting', 'summarize_standard'],
        'medium' => ['draft_note', 'prepare_quote_scope', 'generate_checklist'],
        'high' => ['create_invoice', 'send_message', 'delete_record', 'run_campaign'],
    ],

    // Circuit breaker — per-company, per-tool error rate guard
    'circuit_breaker' => [
        'error_threshold_percent' => 50,   // % errors before tripping
        'window_minutes'          => 10,   // rolling window duration
        'min_calls'               => 5,    // minimum calls before evaluating
        'open_duration_minutes'   => 5,    // how long circuit stays open
    ],

    // Per-company rate limits (defaults; overridden by company_ai_rate_limits table)
    'rate_limits' => [
        'requests_per_minute' => 20,
        'requests_per_day'    => 1000,
        'tokens_per_day'      => 500000,
    ],

    // Evaluation framework
    'evaluation' => [
        'schedule' => 'weekly',
        'sample_size' => 10,  // calls sampled per agent per evaluation run
    ],
];
