<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <div style="background: #4CAF50; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0;">
            <h1 style="margin: 0;">Confirmation d'abonnement</h1>
        </div>

        <div style="background: #f9f9f9; padding: 30px; border-radius: 0 0 5px 5px;">
            <p style="margin: 0 0 20px 0;">Bonjour <strong>{{ $user->name }}</strong>,</p>

            <p style="margin: 0 0 20px 0;">Nous vous confirmons la création de votre abonnement.</p>

            <div style="background: white; padding: 20px; border-radius: 5px; margin: 20px 0;">
                <h3 style="margin: 0 0 15px 0;">Détails de votre abonnement</h3>
                <p style="margin: 0 0 10px 0;"><strong>Plan:</strong> {{ $plan->title ?? $plan->name }}</p>
                <p style="margin: 0 0 10px 0;"><strong>ID d'abonnement:</strong> {{ $subscription->stripe_subscription_id }}</p>
                <p style="margin: 0 0 10px 0;"><strong>Statut:</strong> {{ $subscription->stripe_status }}</p>
                <p style="margin: 0 0 10px 0;"><strong>Début:</strong> {{ optional($subscription->current_period_start)->format('d/m/Y') ?? 'Non défini' }}</p>
                <p style="margin: 0 0 10px 0;"><strong>Fin:</strong> {{ optional($subscription->current_period_end)->format('d/m/Y') ?? 'Non défini' }}</p>
            </div>

            <p style="margin: 0 0 20px 0;">Votre prochain paiement sera automatiquement prélevé le {{ optional($subscription->current_period_end)->format('d/m/Y') ?? 'Non défini' }}.</p>

            <p style="margin: 0;">Vous pouvez gérer votre abonnement depuis votre espace personnel.</p>
        </div>

        <div style="text-align: center; margin-top: 30px; color: #666; font-size: 0.9em;">
            <p style="margin: 5px 0;">Pour toute question, contactez notre support à support@votredomaine.com</p>
            <p style="margin: 5px 0;">&copy; {{ date('Y') }} Votre Société</p>
        </div>
    </div>
</body>
</html>
