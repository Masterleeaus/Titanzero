<?php

namespace Modules\TitanZero\Services\Logging;

use Modules\TitanZero\Entities\TitanZeroAuditLog;

class AuditLogger
{
    public function log(?int $userId, string $action, ?string $route, ?string $ip, array $meta = []): void
    {
        TitanZeroAuditLog::query()->create([
            'user_id' => $userId,
            'action'  => $action,
            'route'   => $route,
            'ip'      => $ip,
            'meta'    => $meta,
        ]);
    }
}
