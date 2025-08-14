@component('mail::message')
# Réinitialisation de votre mot de passe

Vous recevez cet e-mail car nous avons reçu une demande de réinitialisation de mot de passe pour votre compte.

Cliquez sur le bouton ci-dessous pour réinitialiser votre mot de passe :

@component('mail::button', ['url' => $resetUrl])
Réinitialiser le mot de passe
@endcomponent

Si vous n'avez pas demandé de réinitialisation de mot de passe, aucune action n'est requise.

Ce lien de réinitialisation de mot de passe expirera dans 60 minutes.

Merci,
{{ config('app.name') }}
@endcomponent
