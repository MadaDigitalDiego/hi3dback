<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription confirmation</title>
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
                                        <div style="font-family:MonoSans, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Arial, sans-serif; font-size:18px; font-weight:700; line-height:1.25; color:#111827;">Subscription confirmed</div>
                                        <div style="margin-top:6px; font-family:MonoSans, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Arial, sans-serif; font-size:13px; line-height:1.6; color:#6B7280;">{{ config('app.name') }}</div>
                                    </td>
                                </tr>
                            </table>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="padding:24px; font-family:MonoSans, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Arial, sans-serif; color:#111827;">
                                        <p style="margin:0 0 16px 0; font-size:14px; line-height:1.7; color:#111827;">Hello <strong>{{ $user->name }}</strong>,</p>
                                        <p style="margin:0 0 16px 0; font-size:14px; line-height:1.7; color:#374151;">Your subscription has been successfully created.</p>

                                        <p style="margin:0 0 10px 0; font-size:14px; line-height:1.7; font-weight:700; color:#111827;">Subscription details</p>

                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:separate; border-spacing:0; background-color:#F9FAFB; border:1px solid #E5E7EB; border-radius:10px; overflow:hidden;">
                                            <tr>
                                                <td style="padding:10px 12px; font-size:13px; line-height:1.6; font-weight:700; color:#111827; width:160px; border-bottom:1px solid #E5E7EB;">Plan</td>
                                                <td style="padding:10px 12px; font-size:13px; line-height:1.6; color:#374151; border-bottom:1px solid #E5E7EB;">{{ $plan->title ?? $plan->name }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:10px 12px; font-size:13px; line-height:1.6; font-weight:700; color:#111827; width:160px; border-bottom:1px solid #E5E7EB;">Subscription ID</td>
                                                <td style="padding:10px 12px; font-size:13px; line-height:1.6; color:#374151; border-bottom:1px solid #E5E7EB;">{{ $subscription->stripe_subscription_id }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:10px 12px; font-size:13px; line-height:1.6; font-weight:700; color:#111827; width:160px; border-bottom:1px solid #E5E7EB;">Status</td>
                                                <td style="padding:10px 12px; font-size:13px; line-height:1.6; color:#374151; border-bottom:1px solid #E5E7EB;">{{ $subscription->stripe_status }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:10px 12px; font-size:13px; line-height:1.6; font-weight:700; color:#111827; width:160px; border-bottom:1px solid #E5E7EB;">Start</td>
                                                <td style="padding:10px 12px; font-size:13px; line-height:1.6; color:#374151; border-bottom:1px solid #E5E7EB;">{{ optional($subscription->current_period_start)->format('d/m/Y') ?? 'Not defined' }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:10px 12px; font-size:13px; line-height:1.6; font-weight:700; color:#111827; width:160px;">End</td>
                                                <td style="padding:10px 12px; font-size:13px; line-height:1.6; color:#374151;">{{ optional($subscription->current_period_end)->format('d/m/Y') ?? 'Not defined' }}</td>
                                            </tr>
                                        </table>

                                        <p style="margin:16px 0 0 0; font-size:14px; line-height:1.7; color:#374151;">Your next payment will be charged automatically on {{ optional($subscription->current_period_end)->format('d/m/Y') ?? 'Not defined' }}.</p>
                                        <p style="margin:16px 0 0 0; font-size:14px; line-height:1.7; color:#374151;">You can manage your subscription from your dashboard.</p>
                                    </td>
                                </tr>
                            </table>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center" style="background-color:#F9FAFB; border-top:1px solid #E5E7EB; padding:20px 24px;">
                                        <p style="margin:0; font-family:MonoSans, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Arial, sans-serif; font-size:12px; line-height:1.6; color:#6B7280;">
                                            <strong>{{ config('mail.from.name') ?? config('app.name') }}</strong>
                                        </p>
                                        <p style="margin:8px 0 0 0; font-family:MonoSans, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Arial, sans-serif; font-size:12px; line-height:1.6; color:#6B7280;">
                                            For support, contact us at {{ config('mail.from.address') ?? 'support@yourdomain.com' }}
                                        </p>
                                        <p style="margin:8px 0 0 0; font-family:MonoSans, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Arial, sans-serif; font-size:12px; line-height:1.6; color:#6B7280;">
                                            &copy; {{ date('Y') }} {{ config('mail.from.name') ?? config('app.name') }}. All rights reserved.
                                        </p>
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
