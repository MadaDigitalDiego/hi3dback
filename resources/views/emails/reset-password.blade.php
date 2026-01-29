@component('mail::message')
# Reset Your Password

You are receiving this email because we received a password reset request for your account.

Click the button below to reset your password:

@component('mail::button', ['url' => $resetUrl])
Reset Password
@endcomponent

If you did not request a password reset, no action is required.

This password reset link will expire in 60 minutes.

Thank you,
{{ config('app.name') }}
@endcomponent
