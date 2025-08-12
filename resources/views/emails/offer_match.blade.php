<!-- resources/views/emails/offer_match.blade.php -->
@component('mail::message')
# Nouvelle offre correspondant à votre profil

**{{ $offer->title }}**
{{ $offer->description }}

@component('mail::button', ['url' => route('offers.show', $offer->id)])
Voir l'offre
@endcomponent

Merci,
{{ config('app.name') }}
@endcomponent
