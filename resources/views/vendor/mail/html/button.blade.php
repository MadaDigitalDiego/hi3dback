@props(['url', 'color' => '#3399FF'])

@php
    $resolvedColor = $color;

    if (is_string($color)) {
        $key = strtolower(trim($color));
        $resolvedColor = match ($key) {
            'primary' => '#3399FF',
            'success' => '#3399FF',
            'error', 'danger' => '#EF4444',
            default => $color,
        };
    }
@endphp

<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin:24px 0;">
    <tr>
        <td align="center">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <td bgcolor="{{ $resolvedColor }}" style="border-radius:10px;">
                        <a href="{{ $url }}" style="display:inline-block; color:#ffffff; text-decoration:none; padding:12px 24px; border-radius:10px; font-family:'Mona_Sans', 'Mona Sans', MonaSans, MonoSans, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Arial, sans-serif; font-size:14px; font-weight:700; letter-spacing:0.2px;">
                            {{ $slot }}
                        </a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
