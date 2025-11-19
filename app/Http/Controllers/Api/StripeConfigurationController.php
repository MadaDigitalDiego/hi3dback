<?php

namespace App\Http\Controllers\Api;

use App\Models\StripeConfiguration;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class StripeConfigurationController extends Controller
{
    /**
     * Récupère la configuration Stripe active (sans les clés sensibles)
     */
    public function show(): JsonResponse
    {
        $config = StripeConfiguration::getActive();

        if (!$config) {
            return response()->json([
                'message' => 'No active Stripe configuration found',
            ], 404);
        }

        return response()->json([
            'data' => [
                'id' => $config->id,
                'public_key' => $config->public_key,
                'mode' => $config->mode,
                'is_active' => $config->is_active,
                'description' => $config->description,
            ],
        ]);
    }

    /**
     * Récupère la clé publique Stripe (pour le frontend)
     */
    public function getPublicKey(): JsonResponse
    {
        $publicKey = StripeConfiguration::getPublicKey();

        if (!$publicKey) {
            return response()->json([
                'message' => 'Stripe public key not configured',
            ], 404);
        }

        return response()->json([
            'public_key' => $publicKey,
        ]);
    }

    /**
     * Met à jour la configuration Stripe (admin only)
     */
    public function update(Request $request): JsonResponse
    {
        $this->authorize('update', StripeConfiguration::class);

        $validated = $request->validate([
            'public_key' => 'required|string',
            'secret_key' => 'required|string',
            'webhook_secret' => 'required|string',
            'mode' => 'required|in:test,live',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
        ]);

        $config = StripeConfiguration::getActive() ?? new StripeConfiguration();
        $config->fill($validated);
        $config->save();

        return response()->json([
            'message' => 'Stripe configuration updated successfully',
            'data' => [
                'id' => $config->id,
                'mode' => $config->mode,
                'is_active' => $config->is_active,
            ],
        ]);
    }
}

