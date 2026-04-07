<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email Address</title>
</head>
<body>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%; margin:0; padding:0; background-color:#F3F4F6;">
        <tr>
            <td align="center" style="padding:32px 16px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px; width:100%; margin:0 auto;">
                    <tr>
                        <td style="background-color:#ffffff; border:1px solid #E5E7EB; border-radius:12px; overflow:hidden;">
                            <!-- Header -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center" style="padding:24px; border-bottom:1px solid #E5E7EB;">
                                        <img src="{{ rtrim((string) config('app.url'), '/') . '/img/logo/logo-Hi3d.svg' }}" width="140" alt="{{ config('app.name') }}" style="display:block; width:140px; max-width:140px; height:auto; margin:0 auto 20px auto; border:0; outline:none; text-decoration:none;">
                                        <div style="font-family:MonoSans, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Arial, sans-serif; font-size:18px; font-weight:700; line-height:1.25; color:#111827;">Verify your email</div>
                                        <div style="margin-top:6px; font-family:MonoSans, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Arial, sans-serif; font-size:13px; line-height:1.6; color:#6B7280;">{{ config('app.name') }}</div>
                                    </td>
                                </tr>
                            </table>

                            <!-- Content -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="padding:24px; font-family:MonoSans, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Arial, sans-serif; color:#111827;">
                                        <p style="margin:0 0 16px 0; font-size:14px; line-height:1.7; color:#111827;">
                                            Hello <strong>{{ $user->name ?? $user->first_name ?? 'user' }}</strong>,
                                        </p>

                                        <p style="margin:0 0 16px 0; font-size:14px; line-height:1.7; color:#374151;">
                                            Thank you for signing up for <strong>{{ config('app.name') }}</strong>.
                                        </p>

                                        <p style="margin:0 0 24px 0; font-size:14px; line-height:1.7; color:#374151;">
                                            To finish setting up your account, please confirm your email address.
                                        </p>

                                        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin:24px 0;">
                                            <tr>
                                                <td align="center">
                                                    <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                                        <tr>
                                                            <td bgcolor="#3399FF" style="border-radius:6px;">
                                                                <a href="{{ $verificationUrl }}" style="display:inline-block; color:#ffffff; text-decoration:none; padding:12px 20px; border-radius:6px; font-family:MonoSans, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Arial, sans-serif; font-size:14px; font-weight:700;">
                                                                    Verify my email address
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>

                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 16px 0;">
                                            <tr>
                                                <td style="background-color:#F9FAFB; border:1px solid #E5E7EB; border-left:4px solid #3399FF; border-radius:8px; padding:16px; font-family:MonoSans, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Arial, sans-serif; font-size:12px; line-height:1.7; color:#374151;">
                                                    <strong>Security notice:</strong> This link expires in 60 minutes.
                                                </td>
                                            </tr>
                                        </table>

                                        <p style="margin:0 0 10px 0; font-size:14px; line-height:1.7; color:#374151;">
                                            If the button doesn't work, copy and paste the link below into your browser:
                                        </p>
                                        <p style="margin:0 0 24px 0; font-size:12px; line-height:1.7; color:#3399FF; word-break:break-all;">
                                            <a href="{{ $verificationUrl }}" style="color:#3399FF; text-decoration:none;">{{ $verificationUrl }}</a>
                                        </p>

                                        <p style="margin:0; font-size:14px; line-height:1.7; color:#374151;">
                                            If you did not create an account on {{ config('app.name') }}, you can safely ignore this email.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Footer -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center" style="background-color:#F9FAFB; border-top:1px solid #E5E7EB; padding:20px 24px;">
                                        <p style="margin:0; font-family:MonoSans, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Arial, sans-serif; font-size:12px; line-height:1.6; color:#6B7280;">
                                            <strong>{{ config('app.name') }}</strong>
                                        </p>
                                        <p style="margin:8px 0 0 0; font-family:MonoSans, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Arial, sans-serif; font-size:12px; line-height:1.6; color:#6B7280;">
                                            This email was sent to {{ $user->email ?? 'your email address' }}
                                        </p>
                                        <p style="margin:8px 0 0 0; font-family:MonoSans, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Arial, sans-serif; font-size:12px; line-height:1.6; color:#6B7280;">
                                            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
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
