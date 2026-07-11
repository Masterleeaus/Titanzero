<?php

namespace Modules\TitanZero\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TitanZeroContentGenerated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public ?int $userId;
    public ?int $companyId;
    public ?int $templateId;
    public string $content;

    public function __construct(?int $userId, ?int $companyId, ?int $templateId, string $content)
    {
        $this->userId    = $userId;
        $this->companyId = $companyId;
        $this->templateId = $templateId;
        $this->content   = $content;
    }
}
