<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\CartService;
use App\Services\AutoApplyCampaignService;

class SyncCartCampaign
{
    public function handle(Request $request, Closure $next)
    {
        $cartService = app(CartService::class);

        if (method_exists($cartService, 'forgetCache')) {
            $cartService->forgetCache();
        }

        app(AutoApplyCampaignService::class)->sync();

        return $next($request);
    }
}
