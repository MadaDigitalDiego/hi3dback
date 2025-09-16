<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class GmailConfiguration extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'client_id',
        'client_secret',
        'redirect_uri',
        'scopes',
        'is_active',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'scopes' => 'array',
        'is_active' => 'boolean',
        'client_secret' => 'encrypted',
    ];

    /**
     * Récupérer la configuration active
     */
    public static function getActiveConfiguration()
    {
        return static::where('is_active', true)->first();
    }

    /**
     * Vérifier si la configuration est complète
     */
    public function isComplete(): bool
    {
        return !empty($this->client_id) &&
               !empty($this->client_secret) &&
               !empty($this->redirect_uri);
    }

    /**
     * Obtenir les scopes par défaut
     */
    public static function getDefaultScopes(): array
    {
        return ['openid', 'profile', 'email'];
    }
}
