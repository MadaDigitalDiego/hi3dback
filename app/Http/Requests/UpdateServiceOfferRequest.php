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
    //     // Vérifier si l'utilisateur est le propriétaire du service
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
            'price_unit' => 'sometimes|string|in:par image,par m2,par projet',
            'categories' => 'sometimes|array',
            'categories.*' => 'string|max:255', // Each category should be a string
            'execution_time' => 'sometimes|string',
            'concepts' => 'sometimes|string',
            'revisions' => 'sometimes|string',
            'is_private' => 'sometimes|boolean',
            'status' => 'sometimes|string|in:published,draft,pending',
            'files' => 'sometimes|array', // Files array
            'files.*' => 'sometimes|file|max:20480|mimes:jpeg,png,jpg,gif,svg,webp,pdf,doc,docx,xls,xlsx,ppt,pptx,zip,rar', // Each file validation - sometimes ensures validation only runs when item is present
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
            'title.string' => 'Le titre de l\'offre de service doit être une chaîne de caractères.',
            'title.max' => 'Le titre de l\'offre de service ne doit pas dépasser 255 caractères.',
            'price.numeric' => 'Le prix doit être un nombre.',
            'price.min' => 'Le prix doit être un nombre positif.',
            'status.in' => 'Le statut doit être l\'un des suivants : published, draft, pending.',
        ];
    }
}
