<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Plan;
use App\Models\User;
use App\Models\Subscription;
use App\Models\StripeConfiguration;
use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;
use Illuminate\Support\Facades\Log;

class StripeService
{
    protected StripeClient $stripe;

    public function __construct(?StripeClient $client = null)
    {
        if ($client !== null) {
            // Client Stripe injecté (tests, surcharges, etc.)
            $this->stripe = $client;
            return;
        }

        // Récupère la clé secrète depuis la base de données ou la config
        $secretKey = StripeConfiguration::getSecretKey() ?? config('services.stripe.secret');

        // En environnement de tests, on évite de faire échouer l'application
        // si la clé n'est pas configurée : on utilise une clé factice et
        // on court-circuite les appels réseau dans les méthodes concernées.
        if (!$secretKey && app()->environment('testing')) {
            $secretKey = 'sk_test_dummy_for_tests_only';
        }

        if (!$secretKey) {
            throw new \RuntimeException(
                'Stripe secret key is not configured. Please set services.stripe.secret or configure StripeConfiguration.'
            );
        }

        $this->stripe = new StripeClient($secretKey);
    }

    /**
     * Récupère la devise à utiliser pour Stripe (format ISO minuscule).
     */
    protected function getStripeCurrency(): string
    {
        // Devise configurable via config/subscription.php (SUBSCRIPTION_CURRENCY)
        $currency = config('subscription.currency');

        if (! $currency) {
            // Fallback sur le plan "free" si défini, sinon EUR
            $currency = config('subscription.plans.free.currency', 'EUR');
        }

        return strtolower($currency);
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
     * Ensure a given local coupon exists on Stripe and has a Stripe coupon ID.
     *
     * This does not apply the coupon to any subscription; it only syncs metadata.
     */
    public function syncCoupon(Coupon $coupon): void
    {
        // If we already have a Stripe coupon ID, assume it's synced
        if ($coupon->stripe_coupon_id) {
            return;
        }

        // En environnement de tests, on ne contacte jamais Stripe :
        // on assigne simplement un ID factice pour permettre aux flux
        // applicatifs et aux tests de fonctionner sans dépendance réseau.
        if (app()->environment('testing')) {
            $coupon->stripe_coupon_id = 'test_coupon_' . $coupon->id;
            $coupon->save();
            return;
        }

        try {
            $params = [
                'duration' => 'once',
                'metadata' => [
                    'local_coupon_id' => $coupon->id,
                    'local_coupon_code' => $coupon->code,
                ],
                'name' => $coupon->description ?: $coupon->code,
            ];

            if ($coupon->type === 'percentage') {
                $params['percent_off'] = (float) $coupon->value;
            } else {
                $params['amount_off'] = (int) round($coupon->value * 100);
                $params['currency'] = $this->getStripeCurrency();
            }

            if ($coupon->max_uses) {
                $params['max_redemptions'] = $coupon->max_uses;
            }

            if ($coupon->expires_at) {
                $params['redeem_by'] = $coupon->expires_at->getTimestamp();
            }

            $stripeCoupon = $this->stripe->coupons->create($params);

            $coupon->stripe_coupon_id = $stripeCoupon->id;
            $coupon->save();
        } catch (ApiErrorException $e) {
            Log::error('Failed to sync coupon with Stripe: ' . $e->getMessage(), [
                'coupon_id' => $coupon->id ?? null,
                'code' => $coupon->code ?? null,
            ]);

            throw new \Exception('Failed to sync coupon with Stripe: ' . $e->getMessage());
        }
    }

	    /**
	     * Create a subscription for a user.
	     */
	    public function createSubscription(User $user, Plan $plan, ?string $couponCode = null, ?string $billingPeriod = null, ?string $paymentMethodId = null): Subscription
	    {
	        try {
	            $customerId = $this->getOrCreateCustomer($user);
	
	            // Determine the correct Stripe price ID to use for this plan
	            $priceId = null;
	
	            // Use billing_period from request if provided, otherwise fall back to plan's interval
	            $interval = $billingPeriod === 'yearly' ? 'year' : ($billingPeriod === 'monthly' ? 'month' : $plan->interval);
	
	            // Prefer explicit monthly/yearly IDs when available based on the determined interval
	            if ($interval === 'month' && !empty($plan->stripe_price_id_monthly)) {
	                $priceId = $plan->stripe_price_id_monthly;
	            } elseif ($interval === 'year' && !empty($plan->stripe_price_id_yearly)) {
	                $priceId = $plan->stripe_price_id_yearly;
	            }
	
	            // Fallback to legacy field if specific interval price not found
	            if (!$priceId && !empty($plan->stripe_price_id)) {
	                $priceId = $plan->stripe_price_id;
	            }
	
	            if (!$priceId) {
	                throw new \Exception('Plan is not configured with a Stripe price ID for the requested billing period.');
	            }
	
	            // Prepare optional coupon context
	            $stripeCouponId = null;
	            $appliedCoupon = null;
	            $discountAmount = null;
	
	            if ($couponCode) {
	                $appliedCoupon = Coupon::where('code', $couponCode)->first();
	
	                if (!$appliedCoupon) {
	                    throw new \Exception('Coupon not found');
	                }
	
	                if (!$appliedCoupon->isValid()) {
	                    throw new \Exception('Coupon is no longer valid');
	                }
	
	                if (!$appliedCoupon->isApplicableToPlan($plan->id)) {
	                    throw new \Exception('Coupon is not applicable to this plan');
	                }
	
	                if (!$appliedCoupon->canBeUsedByUser($user)) {
	                    throw new \Exception('You have already used this coupon');
	                }
	
	                // Ensure the coupon exists on Stripe and we have its ID
	                $this->syncCoupon($appliedCoupon);
	
	                if (!$appliedCoupon->stripe_coupon_id) {
	                    throw new \Exception('Failed to prepare coupon for Stripe');
	                }
	
	                $stripeCouponId = $appliedCoupon->stripe_coupon_id;
	                $discountAmount = $appliedCoupon->calculateDiscount((float) $plan->price);
	            }
	
	            // Attach payment method to customer if provided
	            if ($paymentMethodId) {
	                try {
	                    // Attach the payment method to the customer
	                    $this->stripe->paymentMethods->attach($paymentMethodId, [
	                        'customer' => $customerId,
	                    ]);
	
	                    // Set as default payment method for the customer
	                    $this->stripe->customers->update($customerId, [
	                        'invoice_settings' => [
	                            'default_payment_method' => $paymentMethodId,
	                        ],
	                    ]);
	                } catch (ApiErrorException $e) {
	                    // If payment method is already attached, that's fine
	                    if (strpos($e->getMessage(), 'already been attached') === false) {
	                        throw new \Exception('Failed to attach payment method: ' . $e->getMessage());
	                    }
	                }
	            }
	
	            $params = [
	                'customer' => $customerId,
	                'items' => [
	                    ['price' => $priceId],
	                ],
	                'expand' => ['latest_invoice.payment_intent'],
	            ];
	
	            // Si une méthode de paiement est fournie, on demande à Stripe d'échouer
	            // immédiatement si le paiement ne peut pas être complété.
	            // Cela évite de créer des abonnements "incomplete" tout en renvoyant
	            // un succès côté API.
	            if ($paymentMethodId) {
	                $params['default_payment_method'] = $paymentMethodId;
	                $params['payment_behavior'] = 'error_if_incomplete';
	            } else {
	                // Cas sans méthode de paiement explicite : on garde le comportement
	                // historique pour ne pas casser d'autres flux éventuels.
	                $params['payment_behavior'] = 'default_incomplete';
	            }
	
	            // Stripe API (versions récentes) n'accepte plus le paramètre direct "coupon".
	            // On doit utiliser "discounts" avec un objet { coupon: <coupon_id> }.
	            if ($stripeCouponId) {
	                $params['discounts'] = [
	                    ['coupon' => $stripeCouponId],
	                ];
	            }
	
	            $stripeSubscription = $this->stripe->subscriptions->create($params);

            // If payment method is provided, try to confirm the payment intent
            if ($paymentMethodId && isset($stripeSubscription->latest_invoice->payment_intent)) {
                $paymentIntent = $stripeSubscription->latest_invoice->payment_intent;

                // Handle different payment intent statuses
                if ($paymentIntent && is_object($paymentIntent)) {
                    $paymentIntentId = is_string($paymentIntent) ? $paymentIntent : $paymentIntent->id;
                    $paymentIntentObj = $this->stripe->paymentIntents->retrieve($paymentIntentId, [
                        'expand' => ['payment_method'],
                    ]);

                    // If payment intent needs payment method or confirmation
                    if (in_array($paymentIntentObj->status, ['requires_payment_method', 'requires_confirmation'])) {
                        try {
                            // Update and confirm the payment intent
                            $this->stripe->paymentIntents->update($paymentIntentId, [
                                'payment_method' => $paymentMethodId,
                            ]);

                            // Confirm the payment intent
                            $confirmedIntent = $this->stripe->paymentIntents->confirm($paymentIntentId);

                            // Refresh the subscription to get updated status
                            $stripeSubscription = $this->stripe->subscriptions->retrieve($stripeSubscription->id, [
                                'expand' => ['latest_invoice.payment_intent'],
                            ]);
                        } catch (ApiErrorException $e) {
                            // If confirmation fails (e.g., 3D Secure required), subscription remains incomplete
                            // This is expected behavior - the frontend should handle 3D Secure
                            Log::warning('Payment intent confirmation failed: ' . $e->getMessage());
                        }
                    }
                }
            }

            // Vérifier que la souscription Stripe est bien active (ou en période d'essai)
            // avant de persister côté base de données. Cela évite de marquer en succès
            // des paiements qui sont en réalité "incomplete" chez Stripe.
            $finalStatus = $stripeSubscription->status;

            $paymentIntentStatus = null;
            if (isset($stripeSubscription->latest_invoice) && isset($stripeSubscription->latest_invoice->payment_intent)) {
                $pi = $stripeSubscription->latest_invoice->payment_intent;
                if (is_object($pi) && isset($pi->status)) {
                    $paymentIntentStatus = $pi->status;
                }
            }

	            if (!in_array($finalStatus, ['active', 'trialing'], true)) {
                Log::warning('Stripe subscription created but not active', [
                    'subscription_id' => $stripeSubscription->id,
                    'status' => $finalStatus,
                    'payment_intent_status' => $paymentIntentStatus,
                ]);

                throw new \Exception(
                    "Le paiement n'a pas pu être finalisé. Votre carte n'a pas été débitée. Veuillez réessayer ou utiliser un autre moyen de paiement."
                );
            }
	
	            $subscriptionData = [
	                'user_id' => $user->id,
	                'plan_id' => $plan->id,
	                'stripe_id' => $stripeSubscription->id,
	                'stripe_subscription_id' => $stripeSubscription->id,
	                'stripe_status' => $stripeSubscription->status,
	                'current_period_start' => $stripeSubscription->current_period_start,
	                'current_period_end' => $stripeSubscription->current_period_end,
	            ];
	
	            if ($appliedCoupon && $discountAmount !== null) {
	                $subscriptionData['coupon_id'] = $appliedCoupon->id;
	                $subscriptionData['discount_amount'] = $discountAmount;
	            }
	
	            $subscription = Subscription::create($subscriptionData);
	
	            if ($appliedCoupon && $discountAmount !== null) {
	                $appliedCoupon->users()->attach($user->id, [
	                    'subscription_id' => $subscription->id,
	                    'discount_amount' => $discountAmount,
	                    'used_at' => now(),
	                ]);
	
	                $appliedCoupon->incrementUsedCount();
	            }
	
	            return $subscription;
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
     * Change subscription to a different plan.
     */
    public function changeSubscription(Subscription $subscription, Plan $newPlan, ?string $billingPeriod = null, ?string $paymentMethodId = null): Subscription
    {
        try {
            // Determine the correct Stripe price ID to use for the new plan
            $priceId = null;
            $interval = $billingPeriod === 'yearly' ? 'year' : ($billingPeriod === 'monthly' ? 'month' : $newPlan->interval);

            // Prefer explicit monthly/yearly IDs when available based on the determined interval
            if ($interval === 'month' && !empty($newPlan->stripe_price_id_monthly)) {
                $priceId = $newPlan->stripe_price_id_monthly;
            } elseif ($interval === 'year' && !empty($newPlan->stripe_price_id_yearly)) {
                $priceId = $newPlan->stripe_price_id_yearly;
            }

            // Fallback to legacy field if specific interval price not found
            if (!$priceId && !empty($newPlan->stripe_price_id)) {
                $priceId = $newPlan->stripe_price_id;
            }

            if (!$priceId) {
                throw new \Exception('New plan is not configured with a Stripe price ID for the requested billing period.');
            }

            // Retrieve current subscription from Stripe
            $stripeSubscription = $this->stripe->subscriptions->retrieve($subscription->stripe_subscription_id);

            // Prepare update parameters
            $updateParams = [
                'items' => [
                    [
                        'id' => $stripeSubscription->items->data[0]->id,
                        'price' => $priceId,
                    ],
                ],
                'proration_behavior' => 'always_invoice', // Prorate the change
            ];

            // Update payment method if provided
            if ($paymentMethodId) {
                try {
                    // Attach the payment method to the customer
                    $this->stripe->paymentMethods->attach($paymentMethodId, [
                        'customer' => $stripeSubscription->customer,
                    ]);

                    // Set as default payment method
                    $this->stripe->customers->update($stripeSubscription->customer, [
                        'invoice_settings' => [
                            'default_payment_method' => $paymentMethodId,
                        ],
                    ]);

                    $updateParams['default_payment_method'] = $paymentMethodId;
                } catch (ApiErrorException $e) {
                    // If payment method is already attached, that's fine
                    if (strpos($e->getMessage(), 'already been attached') === false) {
                        throw new \Exception('Failed to attach payment method: ' . $e->getMessage());
                    }
                }
            }

            // Update the subscription in Stripe
            $updatedSubscription = $this->stripe->subscriptions->update(
                $subscription->stripe_subscription_id,
                $updateParams
            );

            // Update the subscription in the database
            $subscription->update([
                'plan_id' => $newPlan->id,
                'stripe_status' => $updatedSubscription->status,
                'current_period_start' => $updatedSubscription->current_period_start,
                'current_period_end' => $updatedSubscription->current_period_end,
            ]);

            return $subscription->fresh();
        } catch (ApiErrorException $e) {
            throw new \Exception('Failed to change subscription: ' . $e->getMessage());
        }
    }

    /**
     * Update subscription with a coupon.
     */
    public function applyDiscountToSubscription(Subscription $subscription, string $couponCode): void
    {
        try {
	            // Utiliser "discounts" au lieu de "coupon" avec les nouvelles versions de l'API Stripe
	            $this->stripe->subscriptions->update($subscription->stripe_subscription_id, [
	                'discounts' => [
	                    ['coupon' => $couponCode],
	                ],
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
            // Valider les données du plan avant d'appeler Stripe
            $basePrice = (float) $plan->price;

            if ($basePrice <= 0) {
                throw new \Exception('Le prix du plan doit être strictement supérieur à 0 pour créer les prix Stripe. Mettez à jour le champ "Prix mensuel" du plan avant de synchroniser.');
            }

            // Prix annuel : si un prix annuel explicite est saisi, on l'utilise.
            // Sinon, on calcule automatiquement 12 x le prix mensuel.
            $yearlyBasePrice = $basePrice * 12;

            if ($plan->yearly_price !== null) {
                $candidateYearly = (float) $plan->yearly_price;
                if ($candidateYearly > 0) {
                    $yearlyBasePrice = $candidateYearly;
                }
            }

            // Si aucun prix annuel n'est saisi, on en profite pour le renseigner
            // sur le plan pour que l'admin le voie dans le back-office.
            if ($plan->yearly_price === null) {
                $plan->yearly_price = $yearlyBasePrice;
            }

            $currency = $this->getStripeCurrency();

            if (! preg_match('/^[a-z]{3}$/', $currency)) {
                throw new \Exception(sprintf(
                    'Devise Stripe invalide "%s". Configurez SUBSCRIPTION_CURRENCY avec un code ISO à 3 lettres (ex : EUR ou USD).',
                    $currency
                ));
            }

	            // Conserver les anciens IDs de prix pour pouvoir les archiver si on en crée de nouveaux
	            $oldMonthlyPriceId = $plan->stripe_price_id_monthly;
	            $oldYearlyPriceId = $plan->stripe_price_id_yearly;

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

	            // 2) Créer ou mettre à jour les prix Stripe (mensuel & annuel)

	            // Prix mensuel
	            $expectedMonthlyAmount = (int) round($basePrice * 100); // prix par mois en cents
	            $needsNewMonthlyPrice = false;

	            if ($plan->stripe_price_id_monthly) {
	                // Vérifier si le prix Stripe actuel correspond encore au montant attendu
	                try {
	                    $existingMonthly = $this->stripe->prices->retrieve($plan->stripe_price_id_monthly);
	                    $existingAmount = (int) ($existingMonthly->unit_amount ?? 0);
	                    $existingCurrency = strtolower($existingMonthly->currency ?? '');
	                    $existingInterval = $existingMonthly->recurring->interval ?? null;

	                    if ($existingAmount !== $expectedMonthlyAmount
	                        || $existingCurrency !== $currency
	                        || $existingInterval !== 'month') {
	                        $needsNewMonthlyPrice = true;
	                    }
	                } catch (ApiErrorException $e) {
	                    // Prix introuvable ou non accessible : on en recrée un
	                    $needsNewMonthlyPrice = true;
	                }
	            } else {
	                $needsNewMonthlyPrice = true;
	            }

	            if ($needsNewMonthlyPrice) {
	                $monthlyPrice = $this->stripe->prices->create([
	                    'unit_amount' => $expectedMonthlyAmount,
	                    'currency' => $currency,
	                    'recurring' => [
	                        'interval' => 'month',
	                        'interval_count' => 1,
	                    ],
	                    'product' => $plan->stripe_product_id,
	                    'metadata' => [
	                        'plan_id' => $plan->id,
	                        'user_type' => $plan->user_type,
	                        'interval' => 'month',
	                    ],
	                ]);

	                $plan->stripe_price_id_monthly = $monthlyPrice->id;

	                // Archiver l'ancien prix mensuel pour n'avoir qu'un seul tarif actif sur Stripe
	                if ($oldMonthlyPriceId && $oldMonthlyPriceId !== $plan->stripe_price_id_monthly) {
	                    try {
	                        $this->stripe->prices->update($oldMonthlyPriceId, [
	                            'active' => false,
	                        ]);
	                    } catch (ApiErrorException $e) {
	                        // On ignore les erreurs d'archivage pour ne pas bloquer la synchro principale
	                    }
	                }
	            }

	            // Prix annuel (par défaut: 12x le prix mensuel ou valeur saisie dans "Prix annuel")
	            $expectedYearlyAmount = (int) round($yearlyBasePrice * 100);
	            $needsNewYearlyPrice = false;

	            if ($plan->stripe_price_id_yearly) {
	                try {
	                    $existingYearly = $this->stripe->prices->retrieve($plan->stripe_price_id_yearly);
	                    $existingAmount = (int) ($existingYearly->unit_amount ?? 0);
	                    $existingCurrency = strtolower($existingYearly->currency ?? '');
	                    $existingInterval = $existingYearly->recurring->interval ?? null;

	                    if ($existingAmount !== $expectedYearlyAmount
	                        || $existingCurrency !== $currency
	                        || $existingInterval !== 'year') {
	                        $needsNewYearlyPrice = true;
	                    }
	                } catch (ApiErrorException $e) {
	                    // Prix introuvable ou non accessible : on en recrée un
	                    $needsNewYearlyPrice = true;
	                }
	            } else {
	                $needsNewYearlyPrice = true;
	            }

	            if ($needsNewYearlyPrice) {
	                $yearlyPrice = $this->stripe->prices->create([
	                    'unit_amount' => $expectedYearlyAmount,
	                    'currency' => $currency,
	                    'recurring' => [
	                        'interval' => 'year',
	                        'interval_count' => 1,
	                    ],
	                    'product' => $plan->stripe_product_id,
	                    'metadata' => [
	                        'plan_id' => $plan->id,
	                        'user_type' => $plan->user_type,
	                        'interval' => 'year',
	                    ],
	                ]);

	                $plan->stripe_price_id_yearly = $yearlyPrice->id;

	                // Archiver l'ancien prix annuel pour n'avoir qu'un seul tarif actif sur Stripe
	                if ($oldYearlyPriceId && $oldYearlyPriceId !== $plan->stripe_price_id_yearly) {
	                    try {
	                        $this->stripe->prices->update($oldYearlyPriceId, [
	                            'active' => false,
	                        ]);
	                    } catch (ApiErrorException $e) {
	                        // On ignore les erreurs d'archivage pour ne pas bloquer la synchro principale
	                    }
	                }
	            }

            // 3) Compatibilité avec l'ancien champ stripe_price_id (valeur par défaut)
            if (! $plan->stripe_price_id) {
                if ($plan->interval === 'year' && $plan->stripe_price_id_yearly) {
                    $plan->stripe_price_id = $plan->stripe_price_id_yearly;
                } elseif ($plan->stripe_price_id_monthly) {
                    // Par défaut, on pointe sur le prix mensuel
                    $plan->stripe_price_id = $plan->stripe_price_id_monthly;
                }
            }

            $plan->save();

            return $plan;
        } catch (ApiErrorException $e) {
            throw new \Exception('Failed to sync plan with Stripe: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Attach a payment method to a customer.
     */
    public function attachPaymentMethod(string $customerId, string $paymentMethodId): void
    {
        try {
            $this->stripe->paymentMethods->attach($paymentMethodId, [
                'customer' => $customerId,
            ]);
        } catch (ApiErrorException $e) {
            // If payment method is already attached, that's fine
            if (strpos($e->getMessage(), 'already been attached') === false) {
                throw new \Exception('Failed to attach payment method: ' . $e->getMessage());
            }
        }
    }

    /**
     * Set default payment method for a customer.
     */
    public function setDefaultPaymentMethod(string $customerId, string $paymentMethodId): void
    {
        try {
            $this->stripe->customers->update($customerId, [
                'invoice_settings' => [
                    'default_payment_method' => $paymentMethodId,
                ],
            ]);
        } catch (ApiErrorException $e) {
            throw new \Exception('Failed to set default payment method: ' . $e->getMessage());
        }
    }

    /**
     * Retrieve payment method details from Stripe.
     */
    public function getPaymentMethod(string $paymentMethodId)
    {
        try {
            return $this->stripe->paymentMethods->retrieve($paymentMethodId);
        } catch (ApiErrorException $e) {
            throw new \Exception('Failed to retrieve payment method: ' . $e->getMessage());
        }
    }

    /**
     * Detach a payment method from a customer.
     */
    public function detachPaymentMethod(string $paymentMethodId): void
    {
        try {
            $this->stripe->paymentMethods->detach($paymentMethodId);
        } catch (ApiErrorException $e) {
            throw new \Exception('Failed to detach payment method: ' . $e->getMessage());
        }
    }

    /**
     * Update subscription payment method.
     */
    public function updateSubscriptionPaymentMethod(Subscription $subscription, string $paymentMethodId): void
    {
        try {
            // Attach payment method to customer
            $stripeSubscription = $this->stripe->subscriptions->retrieve($subscription->stripe_subscription_id);
            $customerId = $stripeSubscription->customer;

            $this->attachPaymentMethod($customerId, $paymentMethodId);

            // Set as default payment method for customer
            $this->stripe->customers->update($customerId, [
                'invoice_settings' => [
                    'default_payment_method' => $paymentMethodId,
                ],
            ]);

            // Update subscription to use new payment method
            $this->stripe->subscriptions->update($subscription->stripe_subscription_id, [
                'default_payment_method' => $paymentMethodId,
            ]);
        } catch (ApiErrorException $e) {
            throw new \Exception('Failed to update subscription payment method: ' . $e->getMessage());
        }
    }


    /**
     * Create an invoice record in database from Stripe invoice.
     */
    public function createInvoiceFromStripe(User $user, $stripeInvoice, Subscription $subscription = null): Invoice
    {
        try {
            // Générer un numéro de facture unique
            $invoiceNumber = 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -8));
            
            $invoice = Invoice::create([
                'user_id' => $user->id,
                'subscription_id' => $subscription ? $subscription->id : null,
                'stripe_invoice_id' => $stripeInvoice->id,
                'invoice_number' => $invoiceNumber,
                'status' => $stripeInvoice->status,
                'amount' => $stripeInvoice->amount_due / 100, // Convertir de cents
                'tax' => $stripeInvoice->tax / 100 ?? 0,
                'discount' => 0, // À adapter selon vos besoins
                'total' => $stripeInvoice->total / 100,
                'currency' => $stripeInvoice->currency,
                'description' => $stripeInvoice->description ?? 'Abonnement',
                'due_date' => $stripeInvoice->due_date ? \Carbon\Carbon::createFromTimestamp($stripeInvoice->due_date) : null,
                'paid_at' => $stripeInvoice->status === 'paid' ? now() : null,
                'metadata' => json_encode($stripeInvoice->toArray()),
            ]);
            
            return $invoice;
        } catch (\Exception $e) {
            Log::error('Failed to create invoice from Stripe: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate PDF invoice.
     */
    public function generateInvoicePdf(Invoice $invoice, User $user, Subscription $subscription = null): string
    {
        try {
            // Créer le contenu HTML de la facture
            $html = view('pdf.invoice', [
                'invoice' => $invoice,
                'user' => $user,
                'subscription' => $subscription,
            ])->render();
            
            // Chemin de sauvegarde
            $fileName = 'invoices/invoice-' . $invoice->invoice_number . '-' . time() . '.pdf';
            $filePath = storage_path('app/' . $fileName);
            
            // Créer le dossier si nécessaire
            if (!file_exists(storage_path('app/invoices'))) {
                mkdir(storage_path('app/invoices'), 0775, true);
            }
            
            // Utiliser DomPDF pour générer le PDF
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            
            // Sauvegarder le PDF
            file_put_contents($filePath, $dompdf->output());
            
            return $fileName;
        } catch (\Exception $e) {
            Log::error('Failed to generate invoice PDF: ' . $e->getMessage());
            throw new \Exception('Failed to generate invoice PDF');
        }
    }

    /**
     * Send invoice email with PDF attachment.
     */
    public function sendInvoiceEmail(User $user, Invoice $invoice, Subscription $subscription = null): bool
    {
        try {
            // Générer le PDF
            $pdfPath = $this->generateInvoicePdf($invoice, $user, $subscription);
            
            // Envoyer l'email
            \Mail::to($user->email)->send(new \App\Mail\SubscriptionInvoice(
                $user, 
                $invoice, 
                $subscription, 
                $pdfPath
            ));
            
            Log::info('Invoice email sent to user ' . $user->id . ' for invoice ' . $invoice->invoice_number);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send invoice email: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send subscription confirmation email.
     */
    public function sendSubscriptionConfirmation(User $user, Subscription $subscription): bool
    {
        try {
            \Mail::to($user->email)->send(new \App\Mail\SubscriptionConfirmation($user, $subscription));
            
            Log::info('Subscription confirmation sent to user ' . $user->id);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send subscription confirmation: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send subscription cancellation email.
     */
    public function sendSubscriptionCancellation(User $user, Subscription $subscription): bool
    {
        try {
            \Mail::to($user->email)->send(new \App\Mail\SubscriptionCancellation(
                $user, 
                $subscription,
                now()
            ));
            
            Log::info('Subscription cancellation sent to user ' . $user->id);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send subscription cancellation: ' . $e->getMessage());
            return false;
        }
    }


    /**
     * Create or update invoice from Stripe.
     */
    public function syncInvoiceFromStripe($stripeInvoice, User $user, Subscription $subscription = null)
    {
        try {
            // Vérifier si l'invoice existe déjà
            $invoice = \App\Models\Invoice::where('stripe_invoice_id', $stripeInvoice->id)->first();
            
            $invoiceData = [
                'user_id' => $user->id,
                'subscription_id' => $subscription ? $subscription->id : null,
                'stripe_invoice_id' => $stripeInvoice->id,
                'invoice_number' => $stripeInvoice->number ?? 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -8)),
                'status' => $stripeInvoice->status,
                'amount' => $stripeInvoice->subtotal / 100, // Convertir de cents
                'tax' => ($stripeInvoice->tax ?? 0) / 100,
                'discount' => ($stripeInvoice->discount ?? 0) / 100,
                'total' => $stripeInvoice->total / 100,
                'currency' => $stripeInvoice->currency,
                'description' => $stripeInvoice->description ?? 'Subscription payment',
                'due_date' => $stripeInvoice->due_date ? \Carbon\Carbon::createFromTimestamp($stripeInvoice->due_date) : null,
                'paid_at' => $stripeInvoice->status === 'paid' ? \Carbon\Carbon::createFromTimestamp($stripeInvoice->created) : null,
                'metadata' => json_encode($stripeInvoice->toArray()),
            ];
            
            if ($invoice) {
                $invoice->update($invoiceData);
            } else {
                $invoice = \App\Models\Invoice::create($invoiceData);
            }
            
            return $invoice;
            
        } catch (\Exception $e) {
            Log::error('Failed to sync invoice from Stripe: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Récupérer la dernière facture pour un abonnement Stripe.
     */
    public function getLatestStripeInvoiceForSubscription($stripeSubscriptionId)
    {
        try {
            $invoices = $this->stripe->invoices->all([
                'subscription' => $stripeSubscriptionId,
                'limit' => 1,
            ]);
            
            return $invoices->data[0] ?? null;
            
        } catch (ApiErrorException $e) {
            Log::error('Failed to get Stripe invoice: ' . $e->getMessage());
            return null;
        }
    }
}

