<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Stripe\StripeClient;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    protected $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    // public function createSubscription(Request $request)
    // {
    //     try {
    //         // Valider les données de la requête
    //         $request->validate([
    //             'user_id' => 'required|exists:users,id',
    //             'plan_id' => 'required|exists:plans,id',
    //             'stripe_price_id' => 'required|string',
    //             'payment_method_id' => 'required|string',
    //         ]);

    //         // Récupérer l'utilisateur
    //         $user = User::findOrFail($request->user_id);

    //         // Vérifier si l'utilisateur a déjà un stripe_customer_id
    //         if (!$user->stripe_customer_id) {
    //             // Créer un nouveau client Stripe
    //             $stripeCustomer = $this->stripe->customers->create([
    //                 'name' => $user->name,
    //                 'email' => $user->email,
    //             ]);

    //             // Mettre à jour l'utilisateur avec le stripe_customer_id
    //             $user->stripe_customer_id = $stripeCustomer->id;
    //             $user->save();
    //         }

    //         // Attacher la méthode de paiement au client
    //         $this->stripe->paymentMethods->attach($request->payment_method_id, [
    //             'customer' => $user->stripe_customer_id,
    //         ]);

    //         // Définir la méthode de paiement comme méthode par défaut
    //         $this->stripe->customers->update($user->stripe_customer_id, [
    //             'invoice_settings' => [
    //                 'default_payment_method' => $request->payment_method_id,
    //             ],
    //         ]);

    //         // Créer l'abonnement dans Stripe
    //         $stripeSubscription = $this->stripe->subscriptions->create([
    //             'customer' => $user->stripe_customer_id,
    //             'items' => [['price' => $request->stripe_price_id]],
    //             'default_payment_method' => $request->payment_method_id,
    //         ]);

    //         // Créer l'abonnement dans notre base de données
    //         $subscription = Subscription::create([
    //             'user_id' => $request->user_id,
    //             'plan_id' => $request->plan_id,
    //             'stripe_id' => $stripeSubscription->id,
    //             'stripe_status' => $stripeSubscription->status,
    //             'quantity' => 1,
    //             'trial_ends_at' => $stripeSubscription->trial_end ? date('Y-m-d H:i:s', $stripeSubscription->trial_end) : null,
    //             'ends_at' => null,
    //         ]);

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Abonnement créé avec succès',
    //             'data' => $subscription
    //         ], 201);

    //     } catch (\Exception $e) {
    //         Log::error('Erreur lors de la création de l\'abonnement: ' . $e->getMessage());

    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Erreur lors de la création de l\'abonnement',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function createSubscription(Request $request)
    {
        // try {
            Log::info('Requête de création d\'abonnement reçue:', [
                'all_data' => $request->all(),
                'user_id' => $request->user_id,
                'headers' => $request->headers->all()
            ]);

            $request->validate([
                'user_id' => 'required|exists:users,id',
                'plan_id' => 'required|exists:plans,id',
                'stripe_price_id' => 'required|string',
                'payment_method_id' => 'required|string',
            ]);

            $user = User::findOrFail($request->user_id);
            Log::info('Utilisateur trouvé:', ['user' => $user->toArray()]);
            $planPrice = $this->getPlanPrice($request->plan_id); // Vous devez implémenter cette méthode

            // Créer ou récupérer le client Stripe
            if (!$user->stripe_customer_id) {
                $stripeCustomer = $this->stripe->customers->create([
                    'name' => $user->name,
                    'email' => $user->email,
                ]);
                $user->stripe_customer_id = $stripeCustomer->id;
                $user->save();
            }

            // Attacher la méthode de paiement
            $this->stripe->paymentMethods->attach($request->payment_method_id, [
                'customer' => $user->stripe_customer_id,
            ]);

            // Créer le PaymentIntent avec confirmation manuelle
            $paymentIntent = $this->stripe->paymentIntents->create([
                'amount' => $planPrice * 100, // Montant en cents
                'currency' => 'eur',
                'customer' => $user->stripe_customer_id,
                'payment_method' => $request->payment_method_id,
                'off_session' => false, // Le client est présent
                'confirm' => true, // Confirmer immédiatement
                'description' => 'Abonnement ' . $request->plan_id,
                'metadata' => [
                    'user_id' => $user->id,
                    'plan_id' => $request->plan_id,
                ],
                'automatic_payment_methods' => [
                    'enabled' => true,
                    'allow_redirects' => 'never'
                ],
            ]);

            // Si une action supplémentaire est requise (3D Secure)
            if ($paymentIntent->status === 'requires_action') {
                return response()->json([
                    'requires_action' => true,
                    'client_secret' => $paymentIntent->client_secret,
                    'payment_intent_id' => $paymentIntent->id,
                ]);
            }

            // Si le paiement est confirmé immédiatement
            if ($paymentIntent->status === 'succeeded') {
                $subscription = $this->createSubscriptionRecord($user->id, $request->plan_id, $paymentIntent);

                return response()->json([
                    'success' => true,
                    'message' => 'Paiement confirmé avec succès',
                    'data' => $subscription
                ]);
            }

            // Autres cas d'erreur
            return response()->json([
                'success' => false,
                'message' => 'Le paiement n\'a pas pu être traité',
                'error' => 'Statut de paiement inattendu: ' . $paymentIntent->status
            ], 400);
        // } catch (\Exception $e) {
        //     Log::error('Erreur lors de la création de l\'abonnement: ' . $e->getMessage());
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Erreur lors de la création de l\'abonnement',
        //         'error' => $e->getMessage()
        //     ], 500);
        // }
    }

    private function createSubscriptionRecord($userId, $planId, $paymentIntent)
    {
        return Subscription::create([
            'user_id' => $userId,
            'plan_id' => $planId,
            'stripe_id' => $paymentIntent->id,
            'stripe_status' => $paymentIntent->status,
            'quantity' => 1,
            'ends_at' => null,
        ]);
    }

    private function getPlanPrice($planId)
    {
        // Implémentez cette méthode pour retourner le prix du plan
        // Par exemple:
        $plans = [
            1 => 0,    // Free
            2 => 19.99,   // Pro
            3 => 49.99     // Enterprise
        ];

        return $plans[$planId] ?? 0;
    }

    public function confirmPayment(Request $request)
    {
        try {
            $request->validate([
                'payment_intent_id' => 'required|string',
            ]);

            // Récupérer le PaymentIntent
            $paymentIntent = $this->stripe->paymentIntents->retrieve($request->payment_intent_id);

            // Vérifier si le paiement a réussi
            if ($paymentIntent->status === 'succeeded') {
                // Créer l'enregistrement d'abonnement
                $subscription = $this->createSubscriptionRecord(
                    $paymentIntent->metadata->user_id,
                    $paymentIntent->metadata->plan_id,
                    $paymentIntent
                );

                return response()->json([
                    'success' => true,
                    'message' => 'Paiement confirmé avec succès',
                    'data' => $subscription
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Le paiement n\'a pas été confirmé',
                'error' => 'Statut de paiement: ' . $paymentIntent->status
            ], 400);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la confirmation du paiement: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la confirmation du paiement',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
