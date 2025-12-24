<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #4CAF50; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 5px 5px; }
        .plan-details { background: white; padding: 20px; border-radius: 5px; margin: 20px 0; }
        .footer { text-align: center; margin-top: 30px; color: #666; font-size: 0.9em; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Confirmation d'abonnement</h1>
        </div>
        
        <div class="content">
            <p>Bonjour <strong>{{ $user->name }}</strong>,</p>
            
            <p>Nous vous confirmons la création de votre abonnement.</p>
            
            <div class="plan-details">
                <h3>Détails de votre abonnement</h3>
                <p><strong>Plan:</strong> {{ $plan->title ?? $plan->name }}</p>
                <p><strong>ID d'abonnement:</strong> {{ $subscription->stripe_subscription_id }}</p>
                <p><strong>Statut:</strong> {{ $subscription->stripe_status }}</p>
                <p><strong>Début:</strong> {{ optional($subscription->current_period_start)->format('d/m/Y') ?? 'Non défini' }}</p>
                <p><strong>Fin:</strong> {{ optional($subscription->current_period_end)->format('d/m/Y') ?? 'Non défini' }}</p>
            </div>
            
            <p>Votre prochain paiement sera automatiquement prélevé le {{ optional($subscription->current_period_end)->format('d/m/Y') ?? 'Non défini' }}.</p>
            
            <p>Vous pouvez gérer votre abonnement depuis votre espace personnel.</p>
        </div>
        
        <div class="footer">
            <p>Pour toute question, contactez notre support à support@votredomaine.com</p>
            <p>&copy; {{ date('Y') }} Votre Société</p>
        </div>
    </div>
</body>
</html>