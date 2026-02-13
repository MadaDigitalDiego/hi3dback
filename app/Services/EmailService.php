<?php

namespace App\Services;

use App\Models\User;
use App\Mail\VerifyEmail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class EmailService
{
    /**
     * Envoyer un e-mail de vérification à un utilisateur
     *
     * @param User $user L'utilisateur à qui envoyer l'e-mail
     * @return bool True si l'e-mail a été envoyé avec succès, false sinon
     */
    public static function sendVerificationEmail(User $user): bool
    {
        try {
            // URL de redirection vers le frontend
            $frontendUrl = rtrim((string) config('app.frontend_url'), '/');

            // Construction de l'URL de vérification
            $verificationUrl = URL::temporarySignedRoute(
                'verification.verify',
                now()->addMinutes(60), // Expiration dans 60 minutes
                [
                    'id' => $user->id,
                    'hash' => sha1($user->email),
                    'redirect' => urlencode($frontendUrl . '/login?verified=true'), // Ajouter verified=true ici
                ]
            );

            // Vérifier si nous devons sauter la vérification d'e-mail en environnement de développement
            if (env('SKIP_EMAIL_VERIFICATION', false)) {
                $user->markEmailAsVerified();
                Log::info('E-mail automatiquement vérifié (SKIP_EMAIL_VERIFICATION=true) pour ' . $user->email);
                return true;
            }

            // Journaliser l'envoi d'e-mail
            Log::info('Tentative d\'envoi d\'e-mail de vérification à ' . $user->email);

            // Envoi de l'e-mail de vérification (queued)
            Mail::to($user->email)->queue(new VerifyEmail($verificationUrl, $user));
            Log::info('E-mail de vérification envoyé avec succès à ' . $user->email);
            return true;
        } catch (\Exception $e) {
            // Journaliser l'erreur d'envoi d'e-mail
            Log::warning('Erreur lors de l\'envoi de l\'e-mail de vérification à ' . $user->email . ': ' . $e->getMessage());
            Log::warning('Trace: ' . $e->getTraceAsString());

            // En mode debug, afficher plus de détails sur l'erreur
            if (env('APP_DEBUG', false)) {
                Log::error('Détails de l\'erreur d\'envoi d\'e-mail: ' . $e->getMessage());
                Log::error('Trace: ' . $e->getTraceAsString());
            }

            // Marquer l'e-mail comme vérifié uniquement si SKIP_EMAIL_VERIFICATION est activé
            if (env('SKIP_EMAIL_VERIFICATION', false)) {
                $user->markEmailAsVerified();
                Log::info('E-mail automatiquement vérifié malgré l\'erreur (SKIP_EMAIL_VERIFICATION=true) pour ' . $user->email);
                return true;
            }

            return false;
        }
    }
}
