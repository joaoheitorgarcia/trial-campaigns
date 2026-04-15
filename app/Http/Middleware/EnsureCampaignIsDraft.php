<?php

namespace App\Http\Middleware;

use App\Models\Campaign;
use Closure;
use Illuminate\Http\Request;

class EnsureCampaignIsDraft
{
    public function handle(Request $request, Closure $next)
    {
        $routeCampaign = $request->route('campaign');
        $campaign = $routeCampaign instanceof Campaign
            ? $routeCampaign
            : Campaign::findOrFail($routeCampaign);

        if ($campaign->status !== Campaign::STATUS_DRAFT) {
            return response()->json(['error' => 'Campaign must be in draft status.'], 422);
        }

        return $next($request);
    }
}
