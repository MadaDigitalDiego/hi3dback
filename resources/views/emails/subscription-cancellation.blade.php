<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #f44336; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 5px 5px; }
        .plan-details { background: white; padding: 20px; border-radius: 5px; margin: 20px 0; }
        .footer { text-align: center; margin-top: 30px; color: #666; font-size: 0.9em; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Confirmation d'annulation d'abonnement</h1>
        </div>
        
        <div class="content">
            <p>Bonjour <strong>{{ $user->name }}</strong>,</p>
            
            <p>Nous vous confirmons l'annulation de votre abonnement.</p>
            
            <div class="plan-details">
                <h3>Détails de l'abonnement annulé</h3>
                <p><strong>Plan:</strong> {{ $plan->title ?? $plan->name }}</p>
                <p><strong>Date d'annulation:</strong> {{ $cancellationDate->format('d/m/Y') }}</p>
                <p><strong>Statut:</strong> Annulé</p>
            </div>
            
            <p>Votre accès aux fonctionnalités premium restera actif jusqu'à la fin de votre période de facturation actuelle.</p>
            
            <p>Si vous avez changé d'avis, vous pouvez réactiver votre abonnement à tout moment depuis votre espace personnel.</p>
            
            <p>Nous espérons vous revoir bientôt !</p>
        </div>
        
        <div class="footer">
            <p>Pour toute question, contactez notre support à support@votredomaine.com</p>
            <p>&copy; {{ date('Y') }} Votre Société</p>
        </div>
    </div>
</body>
</html>