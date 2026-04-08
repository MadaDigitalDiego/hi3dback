<?php

namespace App\Http\Controllers;

use App\Jobs\ActiveCampaignSyncContactJob;
use App\Services\ActiveCampaignService;
use Illuminate\Http\Request;

class ActiveCampaignController extends Controller
{
    public function syncNow(Request $request, ActiveCampaignService $service)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = $request->input('email');
        $data = $request->only(['firstName', 'lastName', 'phone']);

        // Quick synchronous call (blocking)
        $contactId = $service->syncContact($email, $data);

        return response()->json([
            'ok' => (bool) $contactId,
            'contact_id' => $contactId,
        ]);
    }

    public function syncQueued(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $email = $request->input('email');
        $data = $request->only(['firstName', 'lastName', 'phone']);

        ActiveCampaignSyncContactJob::dispatch($email, $data);

        return response()->json(['ok' => true, 'message' => 'Job dispatched']);
    }
}
