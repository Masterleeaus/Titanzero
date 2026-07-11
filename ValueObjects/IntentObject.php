<?php

namespace Modules\TitanZero\ValueObjects;

class IntentObject
{
    public function __construct(
        public string $intent,
        public int $confidence, // 0..100
        public array $entities = [],
        public array $missing_entities = [],
        public string $risk_level = 'low', // low|medium|high
        public string $execution_mode = 'clarify', // clarify|confirm|execute
        public bool $confirmation_required = true,
        public array $evidence = [],
    ) {}

    public function toArray(): array
    {
        return [
            'intent' => $this->intent,
            'confidence' => $this->confidence,
            'entities' => $this->entities,
            'missing_entities' => $this->missing_entities,
            'risk_level' => $this->risk_level,
            'execution_mode' => $this->execution_mode,
            'confirmation_required' => $this->confirmation_required,
            'evidence' => $this->evidence,
        ];
    }
}
