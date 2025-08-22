<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class HeroImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'image_path',
        'thumbnail_path',
        'is_active',
        'position',
        'alt_text',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'position' => 'integer',
    ];

    /**
     * Scope pour récupérer seulement les images actives
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour ordonner par position
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('position', 'asc')->orderBy('created_at', 'asc');
    }

    /**
     * Obtenir l'URL complète de l'image
     */
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image_path) {
            return null;
        }

        // Si c'est déjà une URL complète, la retourner telle quelle
        if (filter_var($this->image_path, FILTER_VALIDATE_URL)) {
            return $this->image_path;
        }

        // Sinon, générer l'URL complète pour le stockage local
        return asset('storage/' . $this->image_path);
    }

    /**
     * Obtenir l'URL complète de la miniature
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        if (!$this->thumbnail_path) {
            return $this->image_url; // Utiliser l'image principale si pas de miniature
        }

        // Si c'est déjà une URL complète, la retourner telle quelle
        if (filter_var($this->thumbnail_path, FILTER_VALIDATE_URL)) {
            return $this->thumbnail_path;
        }

        // Sinon, générer l'URL complète pour le stockage local
        return asset('storage/' . $this->thumbnail_path);
    }

    /**
     * Supprimer les fichiers associés lors de la suppression du modèle
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($heroImage) {
            // Supprimer l'image principale si elle est stockée localement
            if ($heroImage->image_path && !filter_var($heroImage->image_path, FILTER_VALIDATE_URL)) {
                Storage::disk('public')->delete($heroImage->image_path);
            }

            // Supprimer la miniature si elle est stockée localement
            if ($heroImage->thumbnail_path && !filter_var($heroImage->thumbnail_path, FILTER_VALIDATE_URL)) {
                Storage::disk('public')->delete($heroImage->thumbnail_path);
            }
        });
    }
}
