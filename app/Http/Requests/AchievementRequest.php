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
            'organization' => 'nullable|string|max:255',
            'date_obtained' => 'nullable|date',
            'description' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf,doc,docx,jpeg,png,jpg,gif,svg,webp|max:2048', // Support pour un seul fichier (rétrocompatibilité)
            'files' => 'nullable|array', // Support pour plusieurs fichiers
            'files.*' => 'file|mimes:pdf,doc,docx,jpeg,png,jpg,gif,svg,webp|max:2048', // Validation pour chaque fichier
            'achievement_url' => 'nullable|url|max:255',
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
            'organization.string' => 'L\'organisation doit être une chaîne de caractères.',
            'organization.max' => 'L\'organisation ne doit pas dépasser 255 caractères.',
            'date_obtained.date' => 'La date d\'obtention doit être une date valide.',
            'description.string' => 'La description doit être une chaîne de caractères.',
            'file.file' => 'Le fichier doit être un fichier valide.',
            'file.mimes' => 'Le fichier doit être de type: pdf, doc, docx, jpeg, png, jpg.',
            'file.max' => 'Le fichier ne doit pas dépasser 2048 Ko.',
            'achievement_url.url' => 'L\'URL de la réalisation doit être une URL valide.',
            'achievement_url.max' => 'L\'URL de la réalisation ne doit pas dépasser 255 caractères.',
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
