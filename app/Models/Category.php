<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'value',
        'name',
        'slug',
        'description',
        'parent_id',
        'image_url',
        'count',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'count' => 'integer',
        'order' => 'integer',
    ];

    /**
     * Boot method pour générer automatiquement value et slug
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->value)) {
                $category->value = Str::slug($category->name, '_');
            }
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });

        static::updating(function ($category) {
            if ($category->isDirty('name')) {
                if (empty($category->value)) {
                    $category->value = Str::slug($category->name, '_');
                }
                if (empty($category->slug)) {
                    $category->slug = Str::slug($category->name);
                }
            }
        });
    }

    /**
     * Relation avec la catégorie parente
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Relation avec les sous-catégories
     */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('order');
    }

    /**
     * Récupérer toutes les catégories actives
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Récupérer les catégories principales (sans parent)
     */
    public function scopeMain($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Récupérer les sous-catégories d'une catégorie
     */
    public function scopeChildrenOf($query, $parentId)
    {
        return $query->where('parent_id', $parentId);
    }

    /**
     * Incrémenter le compteur de la catégorie
     */
    public function incrementCount()
    {
        $this->increment('count');

        // Incrémenter aussi le parent si il existe
        if ($this->parent) {
            $this->parent->incrementCount();
        }
    }

    /**
     * Décrémenter le compteur de la catégorie
     */
    public function decrementCount()
    {
        $this->decrement('count');

        // Décrémenter aussi le parent si il existe
        if ($this->parent) {
            $this->parent->decrementCount();
        }
    }

    /**
     * Obtenir le chemin complet de la catégorie
     */
    public function getFullPathAttribute(): string
    {
        $path = [$this->name];
        $parent = $this->parent;

        while ($parent) {
            array_unshift($path, $parent->name);
            $parent = $parent->parent;
        }

        return implode(' > ', $path);
    }

    /**
     * Vérifier si la catégorie a des enfants
     */
    public function hasChildren(): bool
    {
        return $this->children()->count() > 0;
    }

    /**
     * Obtenir tous les descendants (enfants, petits-enfants, etc.)
     */
    public function getAllDescendants()
    {
        $descendants = collect();

        foreach ($this->children as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($child->getAllDescendants());
        }

        return $descendants;
    }
}
