<p style="margin:0; font-family:MonaSans, MonoSans, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Arial, sans-serif; font-size:12px; line-height:1.6; color:#6B7280; text-align:center;">
    {{ $slot }}
</p>
<p style="margin:8px 0 0 0; font-family:MonaSans, MonoSans, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Arial, sans-serif; font-size:12px; line-height:1.6; color:#6B7280; text-align:center;">
    {{ config('mail.from.address') ?? '' }}
</p>
