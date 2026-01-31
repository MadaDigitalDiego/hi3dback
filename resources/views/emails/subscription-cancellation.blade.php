<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <div style="background: #f44336; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0;">
            <h1 style="margin: 0;">Confirmation d'annulation d'abonnement</h1>
        </div>

        <div style="background: #f9f9f9; padding: 30px; border-radius: 0 0 5px 5px;">
            <p style="margin: 0 0 20px 0;">Bonjour <strong>{{ $user->name }}</strong>,</p>

            <p style="margin: 0 0 20px 0;">Nous vous confirmons l'annulation de votre abonnement.</p>

            <div style="background: white; padding: 20px; border-radius: 5px; margin: 20px 0;">
                <h3 style="margin: 0 0 15px 0;">Détails de l'abonnement annulé</h3>
                <p style="margin: 0 0 10px 0;"><strong>Plan:</strong> {{ $plan->title ?? $plan->name }}</p>
                <p style="margin: 0 0 10px 0;"><strong>Date d'annulation:</strong> {{ $cancellationDate->format('d/m/Y') }}</p>
                <p style="margin: 0 0 10px 0;"><strong>Statut:</strong> Annulé</p>
            </div>

            <p style="margin: 0 0 20px 0;">Votre accès aux fonctionnalités premium restera actif jusqu'à la fin de votre période de facturation actuelle.</p>

            <p style="margin: 0 0 20px 0;">Si vous avez changé d'avis, vous pouvez réactiver votre abonnement à tout moment depuis votre espace personnel.</p>

            <p style="margin: 0;">Nous espérons vous revoir bientôt !</p>
        </div>

        <div style="text-align: center; margin-top: 30px; color: #666; font-size: 0.9em;">
            <p style="margin: 5px 0;">Pour toute question, contactez notre support à support@votredomaine.com</p>
            <p style="margin: 5px 0;">&copy; {{ date('Y') }} Votre Société</p>
        </div>
    </div>
</body>
</html>
