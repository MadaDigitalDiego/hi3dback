@component('mail::message')
# Vérifiez votre adresse e-mail

Cliquez sur le bouton ci-dessous pour vérifier votre adresse e-mail.

@component('mail::button', ['url' => $verificationUrl])
Vérifier l'e-mail
@endcomponent

Si vous n'avez pas créé de compte, aucune action n'est requise.

Merci,
{{ config('app.name') }}
@endcomponent
