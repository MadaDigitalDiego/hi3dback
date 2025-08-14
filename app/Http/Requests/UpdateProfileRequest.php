<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;

class UpdateProfileRequest extends FormRequest
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
            // Champs communs
            'first_name' => 'string|max:255',
            'last_name' => 'string|max:255',
            'email' => ['email', Rule::unique('users')->ignore($this->user()->id)],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'bio' => 'nullable|string',
            'avatar' => 'nullable|string',

            // Champs spécifiques au profil professionnel
            'profession' => 'nullable|string|max:255',
            'expertise' => 'nullable|array',
            'expertise.*' => 'string|max:255',
            'years_of_experience' => 'nullable|integer|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
            'title' => 'nullable|string|max:255',
            'skills' => 'nullable',
            'languages' => 'nullable',
            'services_offered' => 'nullable',
            'social_links' => 'nullable',
            'portfolio' => 'nullable',
            'availability_status' => 'nullable|string|in:available,unavailable,busy',

            // Champs spécifiques au profil client
            'type' => 'nullable|in:particulier,entreprise',
            'company_name' => 'nullable|string|required_if:type,entreprise',
            'industry' => 'nullable|string|required_if:type,entreprise',
            'description' => 'nullable|string',
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
            'first_name.string' => 'Le prénom doit être une chaîne de caractères.',
            'first_name.max' => 'Le prénom ne doit pas dépasser 255 caractères.',
            'last_name.string' => 'Le nom de famille doit être une chaîne de caractères.',
            'last_name.max' => 'Le nom de famille ne doit pas dépasser 255 caractères.',
            'email.email' => 'L\'adresse email doit être une adresse email valide.',
            'email.unique' => 'Cette adresse email est déjà utilisée.',
            'profession.string' => 'La profession doit être une chaîne de caractères.',
            'profession.max' => 'La profession ne doit pas dépasser 255 caractères.',
            'expertise.*.string' => 'Chaque expertise doit être une chaîne de caractères.',
            'expertise.*.max' => 'Chaque expertise ne doit pas dépasser 255 caractères.',
            'years_of_experience.integer' => 'Les années d\'expérience doivent être un nombre entier.',
            'years_of_experience.min' => 'Les années d\'expérience doivent être un nombre positif ou nul.',
            'hourly_rate.numeric' => 'Le tarif horaire doit être un nombre.',
            'hourly_rate.min' => 'Le tarif horaire doit être un nombre positif ou nul.',
            'type.in' => 'Le type de profil doit être "particulier" ou "entreprise".',
            'company_name.required_if' => 'Le nom de l\'entreprise est obligatoire lorsque le type de profil est "entreprise".',
            'company_name.string' => 'Le nom de l\'entreprise doit être une chaîne de caractères.',
            'industry.required_if' => 'Le secteur d\'activité est obligatoire lorsque le type de profil est "entreprise".',
            'industry.string' => 'Le secteur d\'activité doit être une chaîne de caractères.',
            'description.string' => 'La description doit être une chaîne de caractères.',
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
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new HttpResponseException(new JsonResponse([
            'errors' => $validator->errors(),
        ], 422));
    }
}
