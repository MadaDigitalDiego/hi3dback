<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Log;

class AchievementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Vous pouvez ajouter une logique d'autorisation plus précise ici si nécessaire
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        Log::info('AchievementRequest rules method - request data:', $this->all());
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:255',
            'cover_photo' => 'required|string|max:255',
            'gallery_photos' => 'nullable|array',
            'gallery_photos.*' => 'string|max:255',
            'youtube_link' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:255',
        ];
    }

    /**
     * Custom message for specific rules
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Le titre de la réalisation est obligatoire.',
            'title.string' => 'Le titre de la réalisation doit être une chaîne de caractères.',
            'title.max' => 'Le titre de la réalisation ne doit pas dépasser 255 caractères.',
            'description.string' => 'La description doit être une chaîne de caractères.',
            'category.string' => 'La catégorie doit être une chaîne de caractères.',
            'category.max' => 'La catégorie ne doit pas dépasser 255 caractères.',
            'cover_photo.required' => 'La photo de couverture est obligatoire.',
            'cover_photo.string' => 'La photo de couverture doit être une chaîne de caractères.',
            'cover_photo.max' => 'La photo de couverture ne doit pas dépasser 255 caractères.',
            'gallery_photos.array' => 'La galerie doit être un tableau.',
            'gallery_photos.*.string' => 'Chaque photo de la galerie doit être une chaîne de caractères.',
            'gallery_photos.*.max' => 'Chaque photo de la galerie ne doit pas dépasser 255 caractères.',
            'youtube_link.string' => 'Le lien YouTube doit être une chaîne de caractères.',
            'youtube_link.max' => 'Le lien YouTube ne doit pas dépasser 255 caractères.',
            'status.string' => 'Le statut doit être une chaîne de caractères.',
            'status.max' => 'Le statut ne doit pas dépasser 255 caractères.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        Log::info('AchievementRequest validation failed.', [
            'errors' => $validator->errors(),
            'request_data' => $this->all() // Log the request data here
        ]);
        throw new HttpResponseException(new JsonResponse([
            'errors' => $validator->errors(),
        ], 422));
    }


}
