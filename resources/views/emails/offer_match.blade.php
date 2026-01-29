<!-- resources/views/emails/offer_match.blade.php -->
@component('mail::message')
# New offer matching your profile

**{{ $offer->title }}**
{{ $offer->description }}

@component('mail::button', ['url' => route('offers.show', $offer->id)])
View Offer
@endcomponent

Thanks,
{{ config('app.name') }}
@endcomponent
