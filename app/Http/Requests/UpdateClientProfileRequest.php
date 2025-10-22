<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClientProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Autorisé car l'authentification est gérée par le middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => 'sometimes|string|in:particulier,entreprise',
            'company_name' => 'sometimes|nullable|string|max:255',
            'industry' => 'sometimes|nullable|string|max:255',
            'description' => 'sometimes|nullable|string',
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255',
            'phone' => 'sometimes|nullable|string|max:20',
            'address' => 'sometimes|nullable|string|max:255',
            'city' => 'sometimes|nullable|string|max:100',
            'country' => 'sometimes|nullable|string|max:100',
            'bio' => 'sometimes|nullable|string',
            'avatar' => 'sometimes|nullable|file|image|max:10240', // Max 2MB
            'birth_date' => 'sometimes|nullable|date',
            'position' => 'sometimes|nullable|string|max:255',
            'company_size' => 'sometimes|nullable|string|max:50',
            'website' => 'sometimes|nullable|string|max:255|url',
            'social_links' => 'sometimes|nullable|array',
            'social_links.linkedin' => 'sometimes|nullable|string|url',
            'social_links.twitter' => 'sometimes|nullable|string|url',
            'social_links.facebook' => 'sometimes|nullable|string|url',
            'social_links.instagram' => 'sometimes|nullable|string|url',
            'preferences' => 'sometimes|nullable|array',
        ];
    }
}
