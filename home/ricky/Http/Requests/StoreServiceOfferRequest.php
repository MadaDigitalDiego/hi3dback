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
            'files' => 'nullable|array', // Files array - removed files.* validation since controller checks hasFile() before processing
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
            'title.required' => 'The service offer title is required.',
            'title.string' => 'The service offer title must be a string.',
            'title.max' => 'The service offer title must not exceed 255 characters.',
            'price.required' => 'The price is required.',
            'price.numeric' => 'The price must be a number.',
            'price.min' => 'The price must be a positive number.',
            'price_unit.required' => 'The price unit is required.',
            'price_unit.in' => 'The price unit must be "per image", "per m2", or "per project".',
            'categories.required' => 'At least one category is required.',
            'categories.array' => 'Categories must be an array.',
            'execution_time.required' => 'The execution time is required.',
            'concepts.required' => 'The number of concepts is required.',
            'revisions.required' => 'The number of revisions is required.',
            'status.required' => 'The status is required.',
            'status.in' => 'The status must be "published", "draft", or "pending".',
        ];
    }

    /**
     * Handle a failed authorization attempt (subscription limits).
     */
    protected function failedAuthorization()
    {
	        $user = $this->user();

	        // If the user does not have an active Stripe subscription, they are on the Free plan
	        // (limits from configuration). The message should reflect that a real subscription
	        // is required to unlock features.
	        $subscription = $user ? $user->currentSubscription() : null;

	        $message = $subscription
	            ? 'You have reached the service creation limit for your subscription. Please upgrade your plan.'
	            : 'Free plan active. A subscription is required to access all features.';

	        throw new HttpResponseException(
	            response()->json([
	                'success' => false,
	                'message' => $message,
	            ], 403)
	        );
    }
}

