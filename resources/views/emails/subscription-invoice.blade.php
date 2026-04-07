<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $invoice->invoice_number }}</title>
</head>
<body>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%; margin:0; padding:0; background-color:#F3F4F6;">
        <tr>
            <td align="center" style="padding:32px 16px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px; width:100%; margin:0 auto;">
                    <tr>
                        <td style="background-color:#ffffff; border:1px solid #E5E7EB; border-radius:12px; overflow:hidden;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center" style="padding:24px; border-bottom:1px solid #E5E7EB;">
                                        <img src="{{ rtrim((string) config('app.url'), '/') . '/img/logo/logo-Hi3d.svg' }}" width="140" alt="{{ config('app.name') }}" style="display:block; width:140px; max-width:140px; height:auto; margin:0 auto 20px auto; border:0; outline:none; text-decoration:none;">
                                        <div style="font-family:MonoSans, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Arial, sans-serif; font-size:18px; font-weight:700; line-height:1.25; color:#111827;">Invoice #{{ $invoice->invoice_number }}</div>
                                        <div style="margin-top:6px; font-family:MonoSans, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Arial, sans-serif; font-size:13px; line-height:1.6; color:#6B7280;">Issue date: {{ $invoice->created_at->format('d/m/Y') }}</div>
                                    </td>
                                </tr>
                            </table>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="padding:24px; font-family:MonoSans, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Arial, sans-serif; color:#111827;">
                                        <p style="margin:0 0 10px 0; font-size:14px; line-height:1.7; font-weight:700; color:#111827;">Invoice details</p>

                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:separate; border-spacing:0; background-color:#F9FAFB; border:1px solid #E5E7EB; border-radius:10px; overflow:hidden;">
                                            <tr>
                                                <td style="padding:10px 12px; font-size:13px; line-height:1.6; font-weight:700; color:#111827; width:160px; border-bottom:1px solid #E5E7EB;">Billed to</td>
                                                <td style="padding:10px 12px; font-size:13px; line-height:1.6; color:#374151; border-bottom:1px solid #E5E7EB;">{{ $user->name }}<br><span style="color:#6B7280;">{{ $user->email }}</span></td>
                                            </tr>
                                            <tr>
                                                <td style="padding:10px 12px; font-size:13px; line-height:1.6; font-weight:700; color:#111827; width:160px; border-bottom:1px solid #E5E7EB;">Status</td>
                                                <td style="padding:10px 12px; font-size:13px; line-height:1.6; color:#374151; border-bottom:1px solid #E5E7EB;">{{ ucfirst($invoice->status) }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:10px 12px; font-size:13px; line-height:1.6; font-weight:700; color:#111827; width:160px;">Due date</td>
                                                <td style="padding:10px 12px; font-size:13px; line-height:1.6; color:#374151;">{{ $invoice->due_date ? $invoice->due_date->format('d/m/Y') : 'N/A' }}</td>
                                            </tr>
                                        </table>

                                        @if($subscription)
                                        <p style="margin:16px 0 10px 0; font-size:14px; line-height:1.7; font-weight:700; color:#111827;">Subscription details</p>
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:separate; border-spacing:0; background-color:#F9FAFB; border:1px solid #E5E7EB; border-radius:10px; overflow:hidden;">
                                            <tr>
                                                <td style="padding:10px 12px; font-size:13px; line-height:1.6; font-weight:700; color:#111827; width:160px; border-bottom:1px solid #E5E7EB;">Plan</td>
                                                <td style="padding:10px 12px; font-size:13px; line-height:1.6; color:#374151; border-bottom:1px solid #E5E7EB;">{{ $subscription->plan->title ?? $subscription->plan->name }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:10px 12px; font-size:13px; line-height:1.6; font-weight:700; color:#111827; width:160px; border-bottom:1px solid #E5E7EB;">Period</td>
                                                <td style="padding:10px 12px; font-size:13px; line-height:1.6; color:#374151; border-bottom:1px solid #E5E7EB;">{{ $subscription->current_period_start->format('d/m/Y') }} - {{ $subscription->current_period_end->format('d/m/Y') }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:10px 12px; font-size:13px; line-height:1.6; font-weight:700; color:#111827; width:160px;">Stripe ID</td>
                                                <td style="padding:10px 12px; font-size:13px; line-height:1.6; color:#374151;">{{ $subscription->stripe_subscription_id }}</td>
                                            </tr>
                                        </table>
                                        @endif

                                        <p style="margin:16px 0 10px 0; font-size:14px; line-height:1.7; font-weight:700; color:#111827;">Charges</p>
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:separate; border-spacing:0; border:1px solid #E5E7EB; border-radius:10px; overflow:hidden;">
                                            <tr>
                                                <th align="left" style="padding:10px 12px; background-color:#F3F4F6; color:#111827; font-weight:700; font-size:13px; line-height:1.6; border-bottom:1px solid #E5E7EB;">Description</th>
                                                <th align="left" style="padding:10px 12px; background-color:#F3F4F6; color:#111827; font-weight:700; font-size:13px; line-height:1.6; border-bottom:1px solid #E5E7EB;">Subtotal</th>
                                                <th align="left" style="padding:10px 12px; background-color:#F3F4F6; color:#111827; font-weight:700; font-size:13px; line-height:1.6; border-bottom:1px solid #E5E7EB;">Tax</th>
                                                <th align="left" style="padding:10px 12px; background-color:#F3F4F6; color:#111827; font-weight:700; font-size:13px; line-height:1.6; border-bottom:1px solid #E5E7EB;">Total</th>
                                            </tr>
                                            <tr>
                                                <td style="padding:10px 12px; font-size:13px; line-height:1.6; color:#374151; border-bottom:1px solid #E5E7EB;">{{ $invoice->description ?? 'Monthly Subscription' }}</td>
                                                <td style="padding:10px 12px; font-size:13px; line-height:1.6; color:#374151; border-bottom:1px solid #E5E7EB;">{{ number_format($invoice->amount, 2, ',', ' ') }} {{ $invoice->currency }}</td>
                                                <td style="padding:10px 12px; font-size:13px; line-height:1.6; color:#374151; border-bottom:1px solid #E5E7EB;">{{ number_format($invoice->tax, 2, ',', ' ') }} {{ $invoice->currency }}</td>
                                                <td style="padding:10px 12px; font-size:13px; line-height:1.6; color:#374151; border-bottom:1px solid #E5E7EB;">{{ number_format($invoice->total, 2, ',', ' ') }} {{ $invoice->currency }}</td>
                                            </tr>
                                        </table>

                                        <p style="margin:14px 0 0 0; font-size:14px; line-height:1.7; font-weight:700; color:#111827; text-align:right;">Total: {{ number_format($invoice->total, 2, ',', ' ') }} {{ $invoice->currency }}</p>
                                    </td>
                                </tr>
                            </table>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center" style="background-color:#F9FAFB; border-top:1px solid #E5E7EB; padding:20px 24px;">
                                        <p style="margin:0; font-family:MonoSans, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Arial, sans-serif; font-size:12px; line-height:1.6; color:#6B7280;">This invoice is also available as a PDF attachment.</p>
                                        <p style="margin:8px 0 0 0; font-family:MonoSans, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Arial, sans-serif; font-size:12px; line-height:1.6; color:#6B7280;">For questions about this invoice, contact us at {{ config('mail.from.address') ?? 'support@yourdomain.com' }}</p>
                                        <p style="margin:8px 0 0 0; font-family:MonoSans, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Arial, sans-serif; font-size:12px; line-height:1.6; color:#6B7280;">&copy; {{ date('Y') }} {{ config('mail.from.name') ?? config('app.name') }}. All rights reserved.</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
