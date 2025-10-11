<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Validation\Validator;

class ClientProfileRequest extends FormRequest
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
            'type' => 'nullable|in:particulier,entreprise',
            'company_name' => 'nullable|string|required_if:type,entreprise',
            'industry' => 'nullable|string|required_if:type,entreprise',
            'description' => 'nullable|string',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:100',
            'bio' => 'nullable|string',
            'birth_date' => 'nullable|date',
            'position' => 'nullable|string|max:255',
            'company_size' => 'nullable|string|max:50',
            'website' => 'nullable|string|max:255',
            'social_links' => 'nullable|array',
            'avatar' => 'nullable|file|image|max:10240',
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
            'type.required' => 'Le type de profil est obligatoire.',
            'type.in' => 'Le type de profil doit être "particulier" ou "entreprise".',
            'company_name.required_if' => 'Le nom de l\'entreprise est obligatoire lorsque le type est "entreprise".',
            'company_name.string' => 'Le nom de l\'entreprise doit être une chaîne de caractères.',
            'industry.required_if' => 'Le secteur d\'activité est obligatoire lorsque le type est "entreprise".',
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
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(new JsonResponse([
            'errors' => $validator->errors(),
        ], 422));
    }
}
