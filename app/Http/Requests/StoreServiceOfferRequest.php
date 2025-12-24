<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreServiceOfferRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        if (!$user) {
            return false;
        }

        // Check subscription limits: users on the free plan (or without
        // subscription) will be blocked here when their quota is 0.
        return $user->canPerformAction('service_offers');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'price_unit' => 'required|string|in:par image,par m2,par projet',
            'categories' => 'required|array', // Expecting an array for categories
            'categories.*' => 'string|max:255', // Each category should be a string
            'execution_time' => 'required|string', // Expecting string for execution_time
            'concepts' => 'required|string', // Expecting string for concepts
            'revisions' => 'required|string', // Expecting string for revisions
            'is_private' => 'boolean', // Expecting boolean for is_private
            'status' => 'required|string|in:published,draft,pending', // Status rule
            'files' => 'nullable|array', // Files array
            'files.*' => 'file|max:20480|mimes:jpeg,png,jpg,gif,svg,webp,pdf,doc,docx,xls,xlsx,ppt,pptx,zip,rar', // Each file validation (added webp)
            'associated_project' => 'nullable|string|max:255',
            'what_you_get' => 'nullable|string',
            'who_is_this_for' => 'nullable|string',
            'delivery_method' => 'nullable|string',
            'why_choose_me' => 'nullable|string',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages()
    {
        return [
            // Customize your error messages here if needed.
            'title.required' => 'Le titre de l\'offre de service est obligatoire.',
            'title.string' => 'Le titre de l\'offre de service doit être une chaîne de caractères.',
            'title.max' => 'Le titre de l\'offre de service ne doit pas dépasser 255 caractères.',
            'price.required' => 'Le prix est obligatoire.',
            'price.numeric' => 'Le prix doit être un nombre.',
            'price.min' => 'Le prix doit être un nombre positif.',
            // Add custom messages for other rules if needed.
        ];
    }

    /**
     * Handle a failed authorization attempt (subscription limits).
     */
    protected function failedAuthorization()
    {
	        $user = $this->user();

	        // Si l'utilisateur n'a pas d'abonnement Stripe actif, il est sur le plan Free
	        // (limites issues de la configuration). Le message doit alors refléter
	        // qu'un véritable abonnement est requis pour débloquer les fonctionnalités.
	        $subscription = $user ? $user->currentSubscription() : null;

	        $message = $subscription
	            ? 'Vous avez atteint la limite de création de services pour votre abonnement. Veuillez mettre à niveau votre plan.'
	            : 'Plan Free actif. Un abonnement est requis pour accéder à toutes les fonctionnalités.';

	        throw new HttpResponseException(
	            response()->json([
	                'success' => false,
	                'message' => $message,
	            ], 403)
	        );
    }
}
