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

        // Clients have unlimited quotas - no subscription required
        if (!$user->is_professional) {
            return response()->json([
                'success' => true,
                'data' => [
                    'subscription' => [
                        'plan_name' => 'Client Unlimited',
                        'plan_slug' => 'client-unlimited',
                        'is_client' => true,
                    ],
                    'usage' => [
                        'service_offers' => [
                            'used' => 0,
                            'limit' => null, // unlimited
                            'percentage' => 0,
                        ],
                        'open_offers' => [
                            'used' => $user->openOffers()->count(),
                            'limit' => null, // unlimited
                            'percentage' => 0,
                        ],
                        'messages' => [
                            'used' => $user->sentMessages()->count(),
                            'limit' => null, // unlimited
                            'percentage' => 0,
                        ],
                        'portfolio_files' => [
                            'used' => $user->portfolioFiles()->count(),
                            'limit' => null, // unlimited
                            'percentage' => 0,
                        ],
                    ],
                    'warnings' => [],
                    'can_upgrade' => false,
                    'message' => 'As a client, you have unlimited access to all features.',
                ],
            ]);
        }

        $subscription = $user->currentSubscription();

        if (!$subscription) {
            return response()->json([
                'success' => false,
	                'message' => 'You must have an active subscription to perform this action.',
	            ], 403);
        }

        $plan = $subscription->plan;
        // Use centralized helper on the User model so limits come from both
        // JSON `limits` and the typed `max_*` columns on Plan.
        $limits = $user->getPlanLimits();

	        // Get current usage (service/open offers are scoped to the current
	        // subscription period via the unified helper on the User model).
	        $usage = [];

	        foreach (['service_offers', 'open_offers'] as $feature) {
	            ['limit' => $rawLimit, 'used' => $used] = $user->getActionLimitAndUsage($feature);
	            $limit = $rawLimit ?? ($limits[$feature] ?? 0);

	            $usage[$feature] = [
	                'used' => $used,
	                'limit' => $limit,
	                'percentage' => $limit > 0
	                    ? round(($used / $limit) * 100, 2)
	                    : 0,
	            ];
	        }

	        // Portfolio files still use a simple per-account limit (no
	        // subscription-period scoping at the moment).
	        $portfolioLimit = $limits['portfolio_files'] ?? 0;
	        $portfolioUsed = $user->portfolioFiles()->count();

	        $usage['portfolio_files'] = [
	            'used' => $portfolioUsed,
	            'limit' => $portfolioLimit,
	            'percentage' => $portfolioLimit > 0
	                ? round(($portfolioUsed / $portfolioLimit) * 100, 2)
	                : 0,
	        ];

        // Calculate warnings
        $warnings = [];
	        foreach ($usage as $feature => $data) {
	            if ($data['percentage'] >= 100) {
	                $warnings[] = [
	                    'feature' => $feature,
	                    'level' => 'critical',
	                    'message' => 'You have reached the limit for your subscription. Please upgrade your plan.',
	                ];
	            } elseif ($data['percentage'] >= 80) {
	                $warnings[] = [
	                    'feature' => $feature,
	                    'level' => 'warning',
	                    'message' => "You have used {$data['percentage']}% of your quota for this feature.",
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

        // Clients have unlimited quotas - always allow actions
        if (!$user->is_professional) {
            return response()->json([
                'success' => true,
                'data' => [
                    'feature' => $feature,
                    'can_perform' => true,
                    'limit' => null, // unlimited
                    'message' => 'En tant que client, vous avez un accès illimité à cette fonctionnalité.',
                    'is_client' => true,
                ],
            ]);
        }

        $subscription = $user->currentSubscription();

        if (!$subscription) {
            return response()->json([
                'success' => false,
	                'message' => 'Vous devez avoir un abonnement actif pour effectuer cette action.',
	            ], 403);
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
	                    ? 'Vous pouvez effectuer cette action.'
	                    : 'Vous avez atteint la limite d’invitations pour votre abonnement. Veuillez mettre à niveau votre plan.',
            ],
        ]);
    }

    /**
     * Get usage percentage for a feature.
     */
    public function getUsagePercentage(string $feature): JsonResponse
    {
        $user = auth()->user();

        // Clients have unlimited quotas
        if (!$user->is_professional) {
            $used = match ($feature) {
                'service_offers' => $user->serviceOffers()->count(),
                'open_offers' => $user->openOffers()->count(),
                'messages' => $user->sentMessages()->count(),
                'portfolio_files' => $user->portfolioFiles()->count(),
                default => 0,
            };

            return response()->json([
                'success' => true,
                'data' => [
                    'feature' => $feature,
                    'used' => $used,
                    'limit' => null, // unlimited
                    'percentage' => 0,
                    'remaining' => null, // unlimited
                    'is_client' => true,
                    'message' => 'En tant que client, vous avez un accès illimité à cette fonctionnalité.',
                ],
            ]);
        }

        $subscription = $user->currentSubscription();

        if (!$subscription) {
            return response()->json([
                'success' => false,
	                'message' => 'Vous devez avoir un abonnement actif pour effectuer cette action.',
	            ], 403);
        }

        // Centralised limits helper (JSON + max_* columns)
        $limits = $user->getPlanLimits();

	        if ($feature === 'portfolio_files') {
	            $used = $user->portfolioFiles()->count();
	            $limit = $limits[$feature] ?? 0;
	        } else {
	            ['limit' => $rawLimit, 'used' => $used] = $user->getActionLimitAndUsage($feature);
	            $limit = $rawLimit ?? ($limits[$feature] ?? 0);
	        }
	
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

