<?php

namespace App\Http\Requests;

use App\Models\FreelanceProfile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Validation\Validator;

class UpdateAvailabilityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Vous pouvez ajouter une logique d'autorisation plus complexe ici si nécessaire
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'availability_status' => ['required', Rule::in([FreelanceProfile::AVAILABILITY_AVAILABLE, FreelanceProfile::AVAILABILITY_UNAVAILABLE])],
            'estimated_response_time' => [
                'nullable',
                'date',
                'after_or_equal:now', // Optionnel : S'assurer que la date est dans le futur ou maintenant
                'required_if:availability_status,' . FreelanceProfile::AVAILABILITY_UNAVAILABLE, // Requis si indisponible
            ],
        ];
    }

    public function messages()
    {
        return [
            'availability_status.required' => 'Le statut de disponibilité est obligatoire.',
            'availability_status.in' => 'Le statut de disponibilité doit être "disponible" ou "indisponible".',
            'estimated_response_time.date' => 'Le délai de réponse estimé doit être une date valide.',
            'estimated_response_time.after_or_equal' => 'Le délai de réponse estimé doit être dans le futur ou maintenant.',
            'estimated_response_time.required_if' => 'Le délai de réponse estimé est obligatoire lorsque le statut est "indisponible".',
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
