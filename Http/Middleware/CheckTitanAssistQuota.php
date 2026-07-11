<?php

namespace Modules\TitanZero\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\TitanZero\Entities\TitanZeroUsage;

class CheckTitanZeroQuota
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        if (! $user) {
            return $next($request);
        }

        $quota = config('aiassistant.quota', []);
        $maxRequests = (int)($quota['daily_requests_per_user'] ?? 0);
        $maxTokens   = (int)($quota['daily_tokens_per_user'] ?? 0);

        if ($maxRequests <= 0 && $maxTokens <= 0) {
            return $next($request);
        }

        $today = now()->startOfDay();
        $query = TitanZeroUsage::where('user_id', $user->id)
            ->where('created_at', '>=', $today);

        $usedRequests = (int) $query->sum('requests_count');
        $usedTokens   = (int) $query->sum('tokens_used');

        if ($maxRequests > 0 && $usedRequests >= $maxRequests) {
            return response()->json([
                'status'  => 'error',
                'message' => __('You have reached your daily Titan Zero request limit.'),
            ]);
        }

        if ($maxTokens > 0 && $usedTokens >= $maxTokens) {
            return response()->json([
                'status'  => 'error',
                'message' => __('You have reached your daily Titan Zero token limit.'),
            ]);
        }

        return $next($request);
    }
}
