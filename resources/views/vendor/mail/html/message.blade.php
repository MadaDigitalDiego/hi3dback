@component('mail::layout')

@slot('header')
@component('mail::header', ['url' => config('app.frontend_url') ?: config('app.url')])
{{ config('app.name') }}
@endcomponent
@endslot

{!! Illuminate\Mail\Markdown::parse($slot) !!}

@isset($subcopy)
@slot('subcopy')
@component('mail::subcopy')
{!! Illuminate\Mail\Markdown::parse($subcopy) !!}
@endcomponent
@endslot
@endisset

@slot('footer')
@component('mail::footer')
© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
@endcomponent
@endslot

@endcomponent
