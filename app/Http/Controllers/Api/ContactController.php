<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log; // Importez la facade Log

class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $contacts = Contact::where('user_id', $user->id)->get();
            return response()->json(['contacts' => $contacts]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération de la liste des contacts: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la récupération des contacts.'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $contact = Contact::create(array_merge($validator->validated(), ['user_id' => $request->user()->id]));
            return response()->json(['contact' => $contact, 'message' => 'Contact ajouté avec succès.'], 201);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'enregistrement d\'un nouveau contact: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de l\'ajout du contact.'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Contact $contact): JsonResponse
    {
        if ($contact->user_id !== auth()->id()) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }
        try {
            return response()->json(['contact' => $contact]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'affichage du contact ID ' . $contact->id . ': ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la récupération du contact.'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Contact $contact): JsonResponse
    {
        if ($contact->user_id !== auth()->id()) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $contact->update($validator->validated());
            return response()->json(['contact' => $contact, 'message' => 'Contact mis à jour avec succès.']);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour du contact ID ' . $contact->id . ': ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la mise à jour du contact.'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contact $contact): JsonResponse
    {
        if ($contact->user_id !== auth()->id()) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }
        try {
            $contact->delete();
            return response()->json(['message' => 'Contact supprimé avec succès.']);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression du contact ID ' . $contact->id . ': ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la suppression du contact.'], 500);
        }
    }
}
