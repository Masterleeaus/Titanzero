<?php

namespace Modules\TitanZero\Support\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * TitanZero Facade
 *
 * The single AI entry point for the platform.
 * Every module and node routes AI requests through this facade.
 *
 * Usage:
 *   TitanZero::query($context, $prompt)
 *
 * @method static array query(array $context, string $prompt)
 *
 * @see \Modules\TitanZero\Services\TitanZeroQueryService
 */
class TitanZero extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'titan-zero.query';
    }
}
