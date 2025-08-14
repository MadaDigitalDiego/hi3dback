<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceOfferRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // You should add your authorization logic here.
        // For example, check if the user has the permission to create service offers.
        return true; // Or your authorization logic, e.g., Auth::user()->can('create', ServiceOffer::class);
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
            'files.*' => 'file|max:10240|mimes:jpeg,png,jpg,gif,svg,webp,pdf,doc,docx,xls,xlsx,ppt,pptx,zip,rar', // Each file validation (added webp)
            'associated_project' => 'nullable|string|max:255',
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
}
