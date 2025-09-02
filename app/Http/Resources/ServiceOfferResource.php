<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceOfferResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Générer l'URL complète de l'image si elle existe
        $imageUrl = null;
        if ($this->image) {
            // Si l'image est déjà une URL complète, la garder telle quelle
            if (filter_var($this->image, FILTER_VALIDATE_URL)) {
                $imageUrl = $this->image;
            } else {
                // Sinon, générer l'URL complète pour le stockage local
                $imageUrl = asset('storage/' . $this->image);
            }
        }

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'first_name' => $this->user->first_name,
                    'last_name' => $this->user->last_name,
                    'email' => $this->user->email,
                    'avatar' => $this->user->avatar,
                    'is_professional' => $this->user->is_professional,
                    'professional_details' => $this->user->freelanceProfile,
                ];
            }),
            'title' => $this->title,
            'description' => $this->description,
            'price' => $this->price,
            'execution_time' => $this->execution_time,
            'concepts' => $this->concepts,
            'revisions' => $this->revisions,
            'is_private' => $this->is_private,
            'categories' => $this->categories,
            'files' => $this->files,
            'image' => $imageUrl, // ✅ Ajout du champ image avec URL complète
            'status' => $this->status,
            'likes' => $this->likes,
            'views' => $this->views,
            'likes_count' => $this->getTotalLikesAttribute(),
            'views_count' => $this->getTotalViewsAttribute(),
            'popularity_score' => $this->getPopularityScore(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
