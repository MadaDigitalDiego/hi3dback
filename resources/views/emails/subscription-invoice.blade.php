<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Facture #{{ $invoice->invoice_number }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="margin: 0 0 10px 0;">Facture #{{ $invoice->invoice_number }}</h1>
            <p style="margin: 0; color: #666;">Date d'émission: {{ $invoice->created_at->format('d/m/Y') }}</p>
        </div>

        <div style="background: #f5f5f5; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <div style="margin-bottom: 10px;">
                        <div style="font-weight: bold; color: #666; margin-bottom: 5px;">Facturé à:</div>
                        <div style="color: #333;">{{ $user->name }}<br>{{ $user->email }}</div>
                    </div>
                </div>
                <div>
                    <div style="margin-bottom: 10px;">
                        <div style="font-weight: bold; color: #666; margin-bottom: 5px;">Statut:</div>
                        <div style="color: #333;">{{ ucfirst($invoice->status) }}</div>
                    </div>
                    <div style="margin-bottom: 10px;">
                        <div style="font-weight: bold; color: #666; margin-bottom: 5px;">Date d'échéance:</div>
                        <div style="color: #333;">{{ $invoice->due_date ? $invoice->due_date->format('d/m/Y') : 'N/A' }}</div>
                    </div>
                </div>
            </div>
        </div>

        @if($subscription)
        <div style="margin-bottom: 20px;">
            <h3 style="margin: 0 0 15px 0;">Détails de l'abonnement</h3>
            <table style="width: 100%; border-collapse: collapse; margin: 0;">
                <tr>
                    <th style="padding: 12px; text-align: left; background: #f8f9fa; border-bottom: 1px solid #ddd;">Plan</th>
                    <td style="padding: 12px; text-align: left; border-bottom: 1px solid #ddd;">{{ $subscription->plan->title ?? $subscription->plan->name }}</td>
                </tr>
                <tr>
                    <th style="padding: 12px; text-align: left; background: #f8f9fa; border-bottom: 1px solid #ddd;">Période</th>
                    <td style="padding: 12px; text-align: left; border-bottom: 1px solid #ddd;">{{ $subscription->current_period_start->format('d/m/Y') }} - {{ $subscription->current_period_end->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <th style="padding: 12px; text-align: left; background: #f8f9fa; border-bottom: 1px solid #ddd;">ID Stripe</th>
                    <td style="padding: 12px; text-align: left; border-bottom: 1px solid #ddd;">{{ $subscription->stripe_subscription_id }}</td>
                </tr>
            </table>
        </div>
        @endif

        <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
            <thead>
                <tr>
                    <th style="padding: 12px; text-align: left; background: #f8f9fa; border-bottom: 1px solid #ddd;">Description</th>
                    <th style="padding: 12px; text-align: left; background: #f8f9fa; border-bottom: 1px solid #ddd;">Montant HT</th>
                    <th style="padding: 12px; text-align: left; background: #f8f9fa; border-bottom: 1px solid #ddd;">Taxe</th>
                    <th style="padding: 12px; text-align: left; background: #f8f9fa; border-bottom: 1px solid #ddd;">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="padding: 12px; text-align: left; border-bottom: 1px solid #ddd;">{{ $invoice->description ?? 'Abonnement mensuel' }}</td>
                    <td style="padding: 12px; text-align: left; border-bottom: 1px solid #ddd;">{{ number_format($invoice->amount, 2, ',', ' ') }} {{ $invoice->currency }}</td>
                    <td style="padding: 12px; text-align: left; border-bottom: 1px solid #ddd;">{{ number_format($invoice->tax, 2, ',', ' ') }} {{ $invoice->currency }}</td>
                    <td style="padding: 12px; text-align: left; border-bottom: 1px solid #ddd;">{{ number_format($invoice->total, 2, ',', ' ') }} {{ $invoice->currency }}</td>
                </tr>
            </tbody>
        </table>

        <div style="text-align: right; font-size: 1.2em; font-weight: bold; margin-top: 20px;">
            Total: {{ number_format($invoice->total, 2, ',', ' ') }} {{ $invoice->currency }}
        </div>

        <div style="margin-top: 30px; text-align: center; color: #666; font-size: 0.9em;">
            <p style="margin: 5px 0;">Cette facture est disponible en pièce jointe au format PDF.</p>
            <p style="margin: 5px 0;">Pour toute question concernant cette facture, veuillez nous contacter à support@votredomaine.com</p>
            <p style="margin: 5px 0;">&copy; {{ date('Y') }} Votre Société. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>
