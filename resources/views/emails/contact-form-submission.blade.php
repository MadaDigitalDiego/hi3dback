<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New contact request</title>
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
                                    <td align="left" style="padding:0; border-bottom:1px solid #E5E7EB;">
                                        <div style="background-color:#3399FF; padding:18px 24px;">
                                            <div style="font-family:MonoSans, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Arial, sans-serif; font-size:14px; font-weight:700; line-height:1.25; color:#ffffff;">{{ config('mail.from.name') ?? config('app.name') }}</div>
                                            <div style="margin-top:4px; font-family:MonoSans, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Arial, sans-serif; font-size:12px; line-height:1.6; color:#EAF4FF;">New contact request</div>
                                        </div>
                                        <div style="padding:18px 24px 0 24px;">
                                            <div style="font-family:MonoSans, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Arial, sans-serif; font-size:18px; font-weight:800; line-height:1.25; color:#111827;">New contact request</div>
                                            <div style="margin-top:6px; font-family:MonoSans, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Arial, sans-serif; font-size:13px; line-height:1.6; color:#6B7280;">A message was submitted from the public contact page.</div>
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="padding:24px; font-family:MonoSans, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Arial, sans-serif; color:#111827;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:16px 0 20px 0;">
                                            <tr>
                                                <td style="background-color:#F9FAFB; border:1px solid #E5E7EB; border-radius:10px; padding:16px;">
                                                    <div style="font-size:12px; line-height:1.6; color:#6B7280; margin:0 0 12px 0; letter-spacing:0.04em;">CONTACT DETAILS</div>

                                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:separate; border-spacing:0;">
                                                        <tr>
                                                            <td style="padding:0 0 10px 0; font-size:13px; line-height:1.6; font-weight:700; color:#111827; width:120px;">Email</td>
                                                            <td style="padding:0 0 10px 0; font-size:13px; line-height:1.6; color:#111827; word-break:break-word;">
                                                                <a href="mailto:{{ $email }}" style="color:#2563EB; text-decoration:none;">{{ $email }}</a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td style="padding:0 0 10px 0; font-size:13px; line-height:1.6; font-weight:700; color:#111827; width:120px;">Phone</td>
                                                            <td style="padding:0 0 10px 0; font-size:13px; line-height:1.6; color:#111827; word-break:break-word;">{{ $phone ?: '—' }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td style="padding:0; font-size:13px; line-height:1.6; font-weight:700; color:#111827; width:120px;">Subject</td>
                                                            <td style="padding:0; font-size:13px; line-height:1.6; color:#111827; word-break:break-word;">{{ $subjectLine }}</td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>

                                        <div style="font-size:12px; line-height:1.6; color:#6B7280; margin:0 0 10px 0; letter-spacing:0.04em;">MESSAGE</div>
                                        <div style="background-color:#ffffff; border:1px solid #E5E7EB; border-left:4px solid #3399FF; border-radius:10px; padding:16px; font-size:14px; line-height:1.75; color:#111827; white-space:pre-wrap;">{{ $bodyMessage }}</div>

                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:18px 0 0 0;">
                                            <tr>
                                                <td style="font-size:12px; line-height:1.6; color:#6B7280;">
                                                    <strong>IP:</strong> {{ $ip ?: '—' }}<br>
                                                    <strong>User agent:</strong> {{ $userAgent ?: '—' }}
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center" style="background-color:#F9FAFB; border-top:1px solid #E5E7EB; padding:20px 24px;">
                                        <p style="margin:0; font-family:MonoSans, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Arial, sans-serif; font-size:12px; line-height:1.6; color:#6B7280;">
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
