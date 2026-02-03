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
        $user = $this->user();

        if (!$user) {
            return false;
        }

        // Check subscription limits for open offers. Users without an active
        // subscription (free plan) will be blocked when their quota is 0.
        return $user->canPerformAction('open_offers');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            // Basic offer information
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'categories' => 'nullable|array',
            'categories.*' => 'string|max:255',
            'budget' => 'nullable|string|max:255',
            'deadline' => 'nullable|date',
            'company' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',

            // Attached files
            'files' => 'nullable|array',
            'files.*' => 'file|max:5120', // 5MB max per file

            // External attachment links (e.g.: Google Drive, Dropbox)
            'attachment_links' => 'nullable|array',
            'attachment_links.*' => 'url|max:2048',

            // Recruitment parameters
            'recruitment_type' => 'required|in:company,personal',
            'open_to_applications' => 'sometimes|boolean',
            'auto_invite' => 'sometimes|boolean',
            'status' => 'sometimes|in:pending,open,closed,in_progress,completed,invited',

            // Filter criteria
            'filters' => 'nullable|array',
            'filters.skills' => 'nullable|array',
            'filters.skills.*' => 'string|max:255',
            'filters.languages' => 'nullable|array',
            'filters.languages.*' => 'string|max:255',
            'filters.location' => 'nullable|string|max:255',
            'filters.experience_years' => 'nullable|integer|min:0',
            'filters.availability_status' => 'nullable|in:available,unavailable',
            'filters.other_criteria' => 'nullable|array', // For potential additional criteria
        ];
    }

    /**
     * Custom validation messages
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            // Messages for basic fields
            'title.required' => 'The offer title is required.',
            'title.max' => 'The title must not exceed 255 characters.',
            'description.required' => 'The description is required.',
            'categories.*.max' => 'A category must not exceed 255 characters.',
            'budget.max' => 'The budget must not exceed 255 characters.',
            'deadline.date' => 'The deadline must be a valid date.',
            'company.max' => 'The company name must not exceed 255 characters.',
            'website.url' => 'The website URL must be valid.',
            'website.max' => 'The URL must not exceed 255 characters.',

            // Messages for files
            'files.*.file' => 'Each file must be valid.',
            'files.*.max' => 'Each file must not exceed 5 MB.',

            // Messages for recruitment
            'recruitment_type.required' => 'The recruitment type is required.',
            'recruitment_type.in' => 'The recruitment type must be "company" or "personal".',
            'status.in' => 'Invalid status.',

            // Messages for filters
            'filters.skills.*.max' => 'A skill must not exceed 255 characters.',
            'filters.languages.*.max' => 'A language must not exceed 255 characters.',
            'filters.location.max' => 'The location must not exceed 255 characters.',
            'filters.experience_years.integer' => 'Experience must be a whole number.',
            'filters.experience_years.min' => 'Experience cannot be negative.',
            'filters.availability_status.in' => 'Invalid availability status.',
        ];
    }

    /**
     * Handle validation errors
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Data validation error'
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY)
        );
    }

    /**
     * Handle authorization failure (subscription limits).
     */
    protected function failedAuthorization()
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'You have reached the limit for creating open offers for your subscription. Please upgrade your plan.',
            ], JsonResponse::HTTP_FORBIDDEN)
        );
    }

    /**
     * Prepare data before validation
     */
    protected function prepareForValidation()
    {
        // Convert JSON strings to arrays if necessary
        if ($this->has('filters') && is_string($this->filters)) {
            $this->merge([
                'filters' => json_decode($this->filters, true)
            ]);
        }
    }
}
