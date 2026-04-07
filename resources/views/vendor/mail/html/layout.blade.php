<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0; padding:0; width:100%; background-color:#F3F4F6; color:#111827; font-family:'Mona_Sans', 'Mona Sans', MonaSans, MonoSans, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Arial, sans-serif; line-height:1.6;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%; background-color:#F3F4F6; padding:32px 0;">
    <tr>
        <td align="center" style="padding:0;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px; width:100%; margin:0 auto;">
                <tr>
                    <td style="padding:0 16px;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#ffffff; border:1px solid #E5E7EB; border-radius:12px; overflow:hidden;">
                            @isset($header)
                            <tr>
                                <td style="background-color:#0D0D0D; background-image:linear-gradient(135deg, #0D0D0D 0%, #0B2D55 55%, #3399FF 110%); padding:26px 24px; text-align:center; border-bottom:1px solid rgba(255,255,255,0.12);">
                                    {{ $header }}
                                </td>
                            </tr>
                            @endisset

                            <tr>
                                <td style="padding:24px; font-size:14px; color:#111827; font-family:'Mona_Sans', 'Mona Sans', MonaSans, MonoSans, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Arial, sans-serif;">
                                    {{ $slot }}
                                </td>
                            </tr>

                            @isset($subcopy)
                            <tr>
                                <td style="padding:0 24px 24px 24px;">
                                    {{ $subcopy }}
                                </td>
                            </tr>
                            @endisset

                            @isset($footer)
                            <tr>
                                <td style="background-color:#F9FAFB; border-top:1px solid #E5E7EB; padding:20px 24px; text-align:center; font-size:12px; color:#6B7280; font-family:'Mona_Sans', 'Mona Sans', MonaSans, MonoSans, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Arial, sans-serif;">
                                    {{ $footer }}
                                </td>
                            </tr>
                            @endisset
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
