<!-- resources/views/emails/offer_match.blade.php -->
@component('mail::message')
# New Offer Matching Your Profile

**{{ $offer->title }}**
{{ $offer->description }}

@component('mail::button', ['url' => route('offers.show', $offer->id)])
View Offer
@endcomponent

Thank you,
{{ config('app.name') }}
@endcomponent
