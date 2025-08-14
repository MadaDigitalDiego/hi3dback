<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Validation\Validator;

class ProfessionalProfileRequest extends FormRequest
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
            'profession' => 'required|string|max:255',
            'expertise' => 'nullable|array',
            'expertise.*' => 'string|max:255', // Validation pour chaque élément du tableau expertise
            'years_of_experience' => 'required|integer|min:0',
            'hourly_rate' => 'required|numeric|min:0',
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
            'profession.required' => 'La profession est obligatoire.',
            'profession.string' => 'La profession doit être une chaîne de caractères.',
            'profession.max' => 'La profession ne doit pas dépasser 255 caractères.',
            'expertise.*.string' => 'Chaque expertise doit être une chaîne de caractères.',
            'expertise.*.max' => 'Chaque expertise ne doit pas dépasser 255 caractères.',
            'years_of_experience.required' => 'Les années d\'expérience sont obligatoires.',
            'years_of_experience.integer' => 'Les années d\'expérience doivent être un nombre entier.',
            'years_of_experience.min' => 'Les années d\'expérience doivent être un nombre positif ou nul.',
            'hourly_rate.required' => 'Le tarif horaire est obligatoire.',
            'hourly_rate.numeric' => 'Le tarif horaire doit être un nombre.',
            'hourly_rate.min' => 'Le tarif horaire doit être un nombre positif ou nul.',
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
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(new JsonResponse([
            'errors' => $validator->errors(),
        ], 422));
    }
}
