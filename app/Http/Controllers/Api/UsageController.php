<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class UsageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Get user's usage statistics.
     */
    public function getUsageStats(): JsonResponse
    {
        $user = auth()->user();
        $subscription = $user->currentSubscription();

        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'No active subscription',
            ], 404);
        }

        $plan = $subscription->plan;
        // Use centralized helper on the User model so limits come from both
        // JSON `limits` and the typed `max_*` columns on Plan.
        $limits = $user->getPlanLimits();

        // Get current usage
        $usage = [
            'service_offers' => [
                'used' => $user->serviceOffers()->count(),
                'limit' => $limits['service_offers'] ?? 0,
                'percentage' => ($limits['service_offers'] ?? 0) > 0
                    ? round(($user->serviceOffers()->count() / $limits['service_offers']) * 100, 2)
                    : 0,
            ],
            'open_offers' => [
                'used' => $user->openOffers()->count(),
                'limit' => $limits['open_offers'] ?? 0,
                'percentage' => ($limits['open_offers'] ?? 0) > 0
                    ? round(($user->openOffers()->count() / $limits['open_offers']) * 100, 2)
                    : 0,
            ],
            'portfolio_files' => [
                'used' => $user->portfolioFiles()->count(),
                'limit' => $limits['portfolio_files'] ?? 0,
                'percentage' => ($limits['portfolio_files'] ?? 0) > 0
                    ? round(($user->portfolioFiles()->count() / $limits['portfolio_files']) * 100, 2)
                    : 0,
            ],
        ];

        // Calculate warnings
        $warnings = [];
        foreach ($usage as $feature => $data) {
            if ($data['percentage'] >= 100) {
                $warnings[] = [
                    'feature' => $feature,
                    'level' => 'critical',
                    'message' => "You have reached the limit for {$feature}",
                ];
            } elseif ($data['percentage'] >= 80) {
                $warnings[] = [
                    'feature' => $feature,
                    'level' => 'warning',
                    'message' => "You are using {$data['percentage']}% of your {$feature} limit",
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'subscription' => [
                    'plan_name' => $plan->name,
                    'plan_slug' => $plan->slug,
                    'current_period_start' => $subscription->current_period_start,
                    'current_period_end' => $subscription->current_period_end,
                ],
                'usage' => $usage,
                'warnings' => $warnings,
                'can_upgrade' => true,
            ],
        ]);
    }

    /**
     * Check if user can perform an action.
     */
    public function canPerformAction(string $feature): JsonResponse
    {
        $user = auth()->user();
        $subscription = $user->currentSubscription();

        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'No active subscription',
            ], 404);
        }

        // Use unified helper so limits are consistent with canPerformAction()
        $limits = $user->getPlanLimits();

        $canPerform = $user->canPerformAction($feature);

        return response()->json([
            'success' => true,
            'data' => [
                'feature' => $feature,
                'can_perform' => $canPerform,
                'limit' => $limits[$feature] ?? 0,
                'message' => $canPerform 
                    ? "You can perform this action"
                    : "You have reached the limit for {$feature}. Please upgrade your plan.",
            ],
        ]);
    }

    /**
     * Get usage percentage for a feature.
     */
    public function getUsagePercentage(string $feature): JsonResponse
    {
        $user = auth()->user();
        $subscription = $user->currentSubscription();

        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'No active subscription',
            ], 404);
        }

        // Centralised limits helper (JSON + max_* columns)
        $limits = $user->getPlanLimits();

        $used = match ($feature) {
            'service_offers' => $user->serviceOffers()->count(),
            'open_offers' => $user->openOffers()->count(),
            'portfolio_files' => $user->portfolioFiles()->count(),
            default => 0,
        };

        $limit = $limits[$feature] ?? 0;
        $percentage = $limit > 0 ? round(($used / $limit) * 100, 2) : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'feature' => $feature,
                'used' => $used,
                'limit' => $limit,
                'percentage' => $percentage,
                'remaining' => max(0, $limit - $used),
            ],
        ]);
    }
}

