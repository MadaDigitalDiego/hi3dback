<?php

namespace App\Policies;

use App\Models\User;
use App\Models\StripeConfiguration;

class StripeConfigurationPolicy
{
    /**
     * Détermine si l'utilisateur peut voir la configuration Stripe
     */
    public function view(User $user): bool
    {
        return $user->is_admin ?? false;
    }

    /**
     * Détermine si l'utilisateur peut mettre à jour la configuration Stripe
     */
    public function update(User $user): bool
    {
        return $user->is_admin ?? false;
    }

    /**
     * Détermine si l'utilisateur peut créer une configuration Stripe
     */
    public function create(User $user): bool
    {
        return $user->is_admin ?? false;
    }

    /**
     * Détermine si l'utilisateur peut supprimer une configuration Stripe
     */
    public function delete(User $user): bool
    {
        return $user->is_admin ?? false;
    }
}

