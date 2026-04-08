<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\ContactFormSubmission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ContactFormController extends Controller
{
    public function submit(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:30',
            'subject' => 'required|string|max:150',
            'message' => 'required|string|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        try {
            $to = config('mail.contact_to') ?: config('mail.from.address');

            if (!$to) {
                Log::error('Contact form submission failed: no recipient configured');
                return response()->json([
                    'message' => 'Email service is not configured.',
                ], 500);
            }

            Mail::to($to)->queue(new ContactFormSubmission(
                $data['email'],
                $data['phone'] ?? null,
                $data['subject'],
                $data['message'],
                $request->ip(),
                $request->userAgent()
            ));

            return response()->json([
                'success' => true,
                'message' => 'Your message has been sent successfully.',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Contact form submission error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Unable to send your message. Please try again later.',
            ], 500);
        }
    }
}
