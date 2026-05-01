<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

class SiteSettingController extends Controller
{
    public function index()
    {
        $settings = SiteSetting::first();

        if (!$settings) {
            return response()->json([
                'about' => [],
                'social_links' => [],
            ]);
        }

        return response()->json([
            'about' => SiteSetting::getAboutContent(),
            'social_links' => SiteSetting::getSocialLinks(),
        ]);
    }
}
