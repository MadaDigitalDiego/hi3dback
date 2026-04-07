@props(['url'])

<a href="{{ $url }}" style="text-decoration:none; display:inline-block;">
    <span style="display:block; font-family:'Mona_Sans', 'Mona Sans', MonaSans, MonoSans, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Arial, sans-serif; font-size:22px; font-weight:800; line-height:1.1; letter-spacing:0.6px; color:#FFFFFF;">
        {{ $slot }}
    </span>
    <span style="display:block; margin-top:6px; font-family:'Mona_Sans', 'Mona Sans', MonaSans, MonoSans, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Arial, sans-serif; font-size:12px; font-weight:500; line-height:1.4; color:rgba(255,255,255,0.78);">
        3D services marketplace
    </span>
</a>
