<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;

class StoreOpenOfferRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            // Informations de base de l'offre
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'categories' => 'nullable|array',
            'categories.*' => 'string|max:255',
            'budget' => 'nullable|string|max:255',
            'deadline' => 'nullable|date',
            'company' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',

            // Fichiers joints
            'files' => 'nullable|array',
            'files.*' => 'file|max:10240', // 2MB max par fichier

            // Paramètres de recrutement
            'recruitment_type' => 'required|in:company,personal',
            'open_to_applications' => 'sometimes|boolean',
            'auto_invite' => 'sometimes|boolean',
            'status' => 'sometimes|in:pending,open,closed,in_progress,completed,invited',

            // Critères de filtrage
            'filters' => 'nullable|array',
            'filters.skills' => 'nullable|array',
            'filters.skills.*' => 'string|max:255',
            'filters.languages' => 'nullable|array',
            'filters.languages.*' => 'string|max:255',
            'filters.location' => 'nullable|string|max:255',
            'filters.experience_years' => 'nullable|integer|min:0',
            'filters.availability_status' => 'nullable|in:available,unavailable',
            'filters.other_criteria' => 'nullable|array', // Pour d'éventuels critères supplémentaires
        ];
    }

    /**
     * Messages de validation personnalisés
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            // Messages pour les champs de base
            'title.required' => 'Le titre de l\'offre est obligatoire.',
            'title.max' => 'Le titre ne doit pas dépasser 255 caractères.',
            'description.required' => 'La description est obligatoire.',
            'categories.*.max' => 'Une catégorie ne doit pas dépasser 255 caractères.',
            'budget.max' => 'Le budget ne doit pas dépasser 255 caractères.',
            'deadline.date' => 'La date limite doit être valide.',
            'company.max' => 'Le nom de l\'entreprise ne doit pas dépasser 255 caractères.',
            'website.url' => 'L\'URL du site web doit être valide.',
            'website.max' => 'L\'URL ne doit pas dépasser 255 caractères.',

            // Messages pour les fichiers
            'files.*.file' => 'Chaque fichier doit être valide.',
            'files.*.max' => 'Chaque fichier ne doit pas dépasser 2 Mo.',

            // Messages pour le recrutement
            'recruitment_type.required' => 'Le type de recrutement est obligatoire.',
            'recruitment_type.in' => 'Le type de recrutement doit être "company" ou "personal".',
            'status.in' => 'Statut invalide.',

            // Messages pour les filtres
            'filters.skills.*.max' => 'Une compétence ne doit pas dépasser 255 caractères.',
            'filters.languages.*.max' => 'Une langue ne doit pas dépasser 255 caractères.',
            'filters.location.max' => 'La localisation ne doit pas dépasser 255 caractères.',
            'filters.experience_years.integer' => 'L\'expérience doit être un nombre entier.',
            'filters.experience_years.min' => 'L\'expérience ne peut pas être négative.',
            'filters.availability_status.in' => 'Statut de disponibilité invalide.',
        ];
    }

    /**
     * Gestion des erreurs de validation
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Erreur de validation des données'
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY)
        );
    }

    /**
     * Préparation des données avant validation
     */
    protected function prepareForValidation()
    {
        // Convertit les chaînes JSON en tableaux si nécessaire
        if ($this->has('filters') && is_string($this->filters)) {
            $this->merge([
                'filters' => json_decode($this->filters, true)
            ]);
        }
    }
}