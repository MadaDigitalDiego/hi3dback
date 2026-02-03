<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceOfferRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    // public function authorize()
    // {
    //     // Check if the user is the owner of the service
    //     $serviceOffer = $this->route('service_offer');
    //     return $serviceOffer && $serviceOffer->user_id === auth()->id();
    // }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules()
    {
        return [
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'price' => 'sometimes|numeric|min:0',
            'price_unit' => 'sometimes|string|in:per image,per m2,per project',
            'categories' => 'sometimes|array',
            'categories.*' => 'string|max:255',
            'execution_time' => 'sometimes|string',
            'concepts' => 'sometimes|string',
            'revisions' => 'sometimes|string',
            'is_private' => 'sometimes|boolean',
            'status' => 'sometimes|string|in:published,draft,pending',
            'files' => 'sometimes|array',
            'files.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:5120',
            'associated_project' => 'sometimes|string|max:255',
            'what_you_get' => 'sometimes|string',
            'who_is_this_for' => 'sometimes|string',
            'delivery_method' => 'sometimes|string',
            'why_choose_me' => 'sometimes|string',
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
            'title.string' => 'The service offer title must be a string.',
            'title.max' => 'The service offer title must not exceed 255 characters.',
            'price.numeric' => 'The price must be a number.',
            'price.min' => 'The price must be a positive number.',
            'price_unit.in' => 'The price unit must be "per image", "per m2" or "per project".',
            'categories.array' => 'Categories must be an array.',
            'categories.*.string' => 'Each category must be a string.',
            'execution_time.string' => 'The execution time must be a string.',
            'concepts.string' => 'The number of concepts must be a string.',
            'revisions.string' => 'The number of revisions must be a string.',
            'status.in' => 'The status must be "published", "draft" or "pending".',
            'files.*.image' => 'Each file must be an image.',
            'files.*.mimes' => 'Each image must be of type: jpeg, png, jpg, gif, svg.',
            'files.*.max' => 'Each image must not exceed 5 MB.',
        ];
    }
}

