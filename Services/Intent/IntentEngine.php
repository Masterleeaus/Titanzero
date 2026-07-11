<?php

namespace Modules\TitanZero\Services\Intent;

use Illuminate\Http\Request;
use Modules\TitanZero\ValueObjects\IntentObject;

class IntentEngine
{
    public function resolve(Request $request, array $pageContext = []): IntentObject
    {
        $text = trim((string)$request->input('text', ''));
        $lc = mb_strtolower($text);

        // Basic deterministic resolver (no AI). We will upgrade to TitanCore-assisted intent later.
        $intent = 'explain_page';
        $confidence = 55;
        $entities = [];
        $missing = [];

        // Detect common intents
        if ($lc === '') {
            $intent = 'clarify';
            $confidence = 0;
            $missing = ['text'];
        } elseif (preg_match('/\bwhat is this\b|\bexplain\b|\bwhat does this mean\b/', $lc)) {
            $intent = 'explain_page';
            $confidence = 85;
        } elseif (preg_match('/\bfill\b|\bhelp me fill\b|\bcomplete\b|\bpopulate\b/', $lc)) {
            $intent = 'help_fill_form';
            $confidence = 82;
        } elseif (preg_match('/\bwhere\b.*\bsetting\b|\bfind\b.*\bsetting\b|\bhow do i\b.*\bsetting\b/', $lc)) {
            $intent = 'find_setting';
            $confidence = 78;
        } elseif (preg_match('/\bstandard\b|\bncc\b|\bas\/nzs\b|\bcode\b/', $lc)) {
            $intent = 'summarize_standard';
            $confidence = 80;
            // entities
            if (preg_match('/\bncc\b/', $lc)) $entities['standard'] = 'NCC';
            if (preg_match('/as\/?nzs\s*([0-9]{3,5})/i', $text, $m)) $entities['standard'] = 'AS/NZS '.$m[1];
        } elseif (preg_match('/\bquote\b|\bscope\b|\bpricing\b/', $lc)) {
            $intent = 'prepare_quote_scope';
            $confidence = 72;
        }

        // Attach page context evidence
        $evidence = [
            'page' => $pageContext['page'] ?? null,
            'route' => $pageContext['route'] ?? null,
            'module' => $pageContext['module'] ?? null,
            'model' => $pageContext['model'] ?? null,
            'fields' => $pageContext['fields'] ?? [],
        ];

        // Determine risk + execution mode
        $risk = $this->riskFor($intent);
        $mode = $this->modeFor($confidence, $risk);

        return new IntentObject(
            intent: $intent,
            confidence: $confidence,
            entities: $entities,
            missing_entities: $missing,
            risk_level: $risk,
            execution_mode: $mode,
            confirmation_required: $mode !== 'execute',
            evidence: $evidence
        );
    }

    protected function riskFor(string $intent): string
    {
        $cfg = config('titanzero.risk', []);
        foreach (['high','medium','low'] as $lvl) {
            if (in_array($intent, $cfg[$lvl] ?? [], true)) return $lvl;
        }
        return 'low';
    }

    protected function modeFor(int $confidence, string $risk): string
    {
        $intentCfg = config('titanzero.intent');
        $auto = (int)($intentCfg['auto_execute_min'] ?? 90);
        $confirm = (int)($intentCfg['confirm_min'] ?? 70);
        $clarify = (int)($intentCfg['clarify_below'] ?? 70);

        if ($confidence < $clarify) return 'clarify';

        if ($risk === 'high') return 'confirm'; // always confirm high-risk

        if ($confidence >= $auto) return 'execute';

        if ($confidence >= $confirm) return 'confirm';

        return 'clarify';
    }
}
