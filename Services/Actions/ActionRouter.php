<?php

namespace Modules\TitanZero\Services\Actions;

use Illuminate\Support\Facades\Auth;
use Modules\TitanZero\Contracts\Actions\ActionHandlerInterface;
use Modules\TitanZero\Entities\TitanZeroAuditLog;
use Modules\TitanZero\Entities\TitanZeroIntentRun;
use Modules\TitanZero\ValueObjects\IntentObject;

class ActionRouter
{
    /** @var ActionHandlerInterface[] */
    protected array $handlers = [];

    public function registerHandler(ActionHandlerInterface $handler): void
    {
        $this->handlers[] = $handler;
    }

    public function route(IntentObject $intent, array $context = []): array
    {
        $run = TitanZeroIntentRun::create([
            'intent' => $intent->intent,
            'confidence' => $intent->confidence,
            'risk_level' => $intent->risk_level,
            'execution_mode' => $intent->execution_mode,
            'entities_json' => json_encode($intent->entities),
            'missing_entities_json' => json_encode($intent->missing_entities),
            'page_context_json' => json_encode($intent->evidence),
            'user_id' => Auth::id(),
            'status' => 'created',
        ]);

        // Clarify / confirm are not executed in this pass (foundation only)
        if ($intent->execution_mode !== 'execute') {
            $this->audit($run->id, 'intent_resolved', ['mode' => $intent->execution_mode]);
            $run->status = 'waiting';
            $run->save();

            return [
                'ok' => true,
                'run_id' => $run->id,
                'intent' => $intent->toArray(),
                'next' => $intent->execution_mode,
            ];
        }

        // Find handler
        $handler = $this->findHandler($intent->intent);
        if (!$handler) {
            $this->audit($run->id, 'no_handler', ['intent' => $intent->intent]);
            $run->status = 'no_handler';
            $run->save();

            return [
                'ok' => false,
                'run_id' => $run->id,
                'error' => 'No handler registered for intent: '.$intent->intent,
                'intent' => $intent->toArray(),
            ];
        }

        $validation = $handler->validate($intent, $context);
        if (!($validation['ok'] ?? false)) {
            $this->audit($run->id, 'validation_failed', $validation);
            $run->status = 'invalid';
            $run->save();

            return [
                'ok' => false,
                'run_id' => $run->id,
                'error' => 'Validation failed',
                'details' => $validation,
                'intent' => $intent->toArray(),
            ];
        }

        $this->audit($run->id, 'execute_begin', []);
        $result = $handler->execute($intent, $context);
        $this->audit($run->id, 'execute_end', ['result' => $result]);

        $run->status = 'executed';
        $run->result_json = json_encode($result);
        $run->save();

        return [
            'ok' => true,
            'run_id' => $run->id,
            'intent' => $intent->toArray(),
            'result' => $result,
        ];
    }

    protected function findHandler(string $intent): ?ActionHandlerInterface
    {
        foreach ($this->handlers as $h) {
            if ($h->supports($intent)) return $h;
        }
        return null;
    }

    protected function audit(int $runId, string $event, array $payload): void
    {
        TitanZeroAuditLog::create([
            'intent_run_id' => $runId,
            'event' => $event,
            'payload_json' => json_encode($payload),
            'user_id' => Auth::id(),
        ]);
    }

    /**
     * Confirm a previously resolved intent run and execute it (if a handler exists).
     */
    public function confirmAndExecute(int $runId, array $context = []): array
    {
        $run = TitanZeroIntentRun::query()->findOrFail($runId);

        $intentArr = [
            'intent' => $run->intent,
            'confidence' => (int)$run->confidence,
            'risk_level' => (string)$run->risk_level,
            'execution_mode' => 'execute',
            'entities' => json_decode($run->entities_json ?: '[]', true) ?: [],
            'missing_entities' => json_decode($run->missing_entities_json ?: '[]', true) ?: [],
            'evidence' => json_decode($run->page_context_json ?: '[]', true) ?: [],
        ];

        $intent = new \Modules\TitanZero\ValueObjects\IntentObject(
            intent: (string)$intentArr['intent'],
            confidence: (int)$intentArr['confidence'],
            entities: (array)$intentArr['entities'],
            missing_entities: (array)$intentArr['missing_entities'],
            risk_level: (string)$intentArr['risk_level'],
            execution_mode: 'execute',
            confirmation_required: false,
            evidence: (array)$intentArr['evidence'],
        );

        $this->audit($run->id, 'confirmed', []);
        // execute path
        $handler = $this->findHandler($intent->intent);
        if (!$handler) {
            $this->audit($run->id, 'no_handler', ['intent' => $intent->intent]);
            $run->status = 'no_handler';
            $run->save();
            return [
                'ok' => false,
                'run_id' => $run->id,
                'error' => 'No handler registered for intent: '.$intent->intent,
                'intent' => $intent->toArray(),
            ];
        }

        $validation = $handler->validate($intent, $context);
        if (!($validation['ok'] ?? false)) {
            $this->audit($run->id, 'validation_failed', $validation);
            $run->status = 'invalid';
            $run->save();
            return [
                'ok' => false,
                'run_id' => $run->id,
                'error' => 'Validation failed',
                'details' => $validation,
                'intent' => $intent->toArray(),
            ];
        }

        $this->audit($run->id, 'execute_begin', []);
        $result = $handler->execute($intent, $context);
        $this->audit($run->id, 'execute_end', ['result' => $result]);

        $run->status = 'executed';
        $run->execution_mode = 'execute';
        $run->result_json = json_encode($result);
        $run->save();

        return [
            'ok' => true,
            'run_id' => $run->id,
            'intent' => $intent->toArray(),
            'result' => $result,
        ];
    }

}
