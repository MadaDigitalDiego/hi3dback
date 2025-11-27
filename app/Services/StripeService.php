<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\User;
use App\Models\Subscription;
use App\Models\StripeConfiguration;
use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;

class StripeService
{
    protected StripeClient $stripe;

    public function __construct()
    {
        // Récupère la clé secrète depuis la base de données ou la config
        $secretKey = StripeConfiguration::getSecretKey() ?? config('services.stripe.secret');
        $this->stripe = new StripeClient($secretKey);
    }

    /**
     * Create a Stripe customer for a user.
     */
    public function createCustomer(User $user): string
    {
        try {
            $customer = $this->stripe->customers->create([
                'email' => $user->email,
                'name' => $user->name,
                'metadata' => [
                    'user_id' => $user->id,
                ],
            ]);

            $user->update(['stripe_customer_id' => $customer->id]);

            return $customer->id;
        } catch (ApiErrorException $e) {
            throw new \Exception('Failed to create Stripe customer: ' . $e->getMessage());
        }
    }

    /**
     * Get or create a Stripe customer for a user.
     */
    public function getOrCreateCustomer(User $user): string
    {
        if ($user->stripe_customer_id) {
            return $user->stripe_customer_id;
        }

        return $this->createCustomer($user);
    }

    /**
     * Create a subscription for a user.
     */
    public function createSubscription(User $user, Plan $plan, ?string $couponCode = null): Subscription
    {
        try {
            $customerId = $this->getOrCreateCustomer($user);

            // Determine the correct Stripe price ID to use for this plan
            $priceId = null;

            // Legacy field
            if (!empty($plan->stripe_price_id)) {
                $priceId = $plan->stripe_price_id;
            }

            // Prefer explicit monthly/yearly IDs when available
            if ($plan->interval === 'month' && !empty($plan->stripe_price_id_monthly)) {
                $priceId = $plan->stripe_price_id_monthly;
            } elseif ($plan->interval === 'year' && !empty($plan->stripe_price_id_yearly)) {
                $priceId = $plan->stripe_price_id_yearly;
            }

            if (!$priceId) {
                throw new \Exception('Plan is not configured with a Stripe price ID.');
            }

            $params = [
                'customer' => $customerId,
                'items' => [
                    ['price' => $priceId],
                ],
                'payment_behavior' => 'default_incomplete',
                'expand' => ['latest_invoice.payment_intent'],
            ];

            if ($couponCode) {
                $params['coupon'] = $couponCode;
            }

            $stripeSubscription = $this->stripe->subscriptions->create($params);

            return Subscription::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'stripe_id' => $stripeSubscription->id,
                'stripe_subscription_id' => $stripeSubscription->id,
                'stripe_status' => $stripeSubscription->status,
                'current_period_start' => $stripeSubscription->current_period_start,
                'current_period_end' => $stripeSubscription->current_period_end,
            ]);
        } catch (ApiErrorException $e) {
            throw new \Exception('Failed to create subscription: ' . $e->getMessage());
        }
    }

    /**
     * Cancel a subscription.
     */
    public function cancelSubscription(Subscription $subscription): void
    {
        try {
            $this->stripe->subscriptions->cancel($subscription->stripe_subscription_id);
            $subscription->cancel();
        } catch (ApiErrorException $e) {
            throw new \Exception('Failed to cancel subscription: ' . $e->getMessage());
        }
    }

    /**
     * Resume a subscription.
     */
    public function resumeSubscription(Subscription $subscription): void
    {
        try {
            $this->stripe->subscriptions->update($subscription->stripe_subscription_id, [
                'pause_collection' => null,
            ]);
            $subscription->resume();
        } catch (ApiErrorException $e) {
            throw new \Exception('Failed to resume subscription: ' . $e->getMessage());
        }
    }

    /**
     * Update subscription with a coupon.
     */
    public function applyDiscountToSubscription(Subscription $subscription, string $couponCode): void
    {
        try {
            $this->stripe->subscriptions->update($subscription->stripe_subscription_id, [
                'coupon' => $couponCode,
            ]);
        } catch (ApiErrorException $e) {
            throw new \Exception('Failed to apply coupon: ' . $e->getMessage());
        }
    }

    /**
     * Get subscription details from Stripe.
     */
    public function getSubscription(string $stripeSubscriptionId)
    {
        try {
            return $this->stripe->subscriptions->retrieve($stripeSubscriptionId);
        } catch (ApiErrorException $e) {
            throw new \Exception('Failed to retrieve subscription: ' . $e->getMessage());
        }
    }

    /**
     * Create Stripe product & price for a plan if missing, and sync IDs.
     *
     * This allows creating the corresponding Product and Price directly
     * depuis le back-office sans passer par le dashboard Stripe.
     *
     * @throws \Exception
     */
    public function syncPlanWithStripe(Plan $plan): Plan
    {
        try {
            // 1) Créer le produit Stripe si manquant
            if (! $plan->stripe_product_id) {
                $product = $this->stripe->products->create([
                    'name' => $plan->title ?? $plan->name,
                    'description' => $plan->description,
                    'metadata' => [
                        'plan_id' => $plan->id,
                        'user_type' => $plan->user_type,
                    ],
                ]);

                $plan->stripe_product_id = $product->id;
            }

            // 2) Créer le prix Stripe si manquant
            if (! $plan->stripe_price_id) {
                // Stripe attend un montant en centimes et une devise ISO en minuscule
                $unitAmount = (int) round(((float) $plan->price) * 100);

                $price = $this->stripe->prices->create([
                    'unit_amount' => $unitAmount,
                    'currency' => 'eur', // à adapter si multi-devise
                    'recurring' => [
                        'interval' => $plan->interval,
                        'interval_count' => $plan->interval_count,
                    ],
                    'product' => $plan->stripe_product_id,
                    'metadata' => [
                        'plan_id' => $plan->id,
                        'user_type' => $plan->user_type,
                    ],
                ]);

                $plan->stripe_price_id = $price->id;
            }

            $plan->save();

            return $plan;
        } catch (ApiErrorException $e) {
            throw new \Exception('Failed to sync plan with Stripe: ' . $e->getMessage(), 0, $e);
        }
    }
}

