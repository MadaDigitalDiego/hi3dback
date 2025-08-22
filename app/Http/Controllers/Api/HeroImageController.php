<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\HeroImageResource;
use App\Models\HeroImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HeroImageController extends Controller
{
    /**
     * Display a listing of active hero images.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        $heroImages = HeroImage::active()
            ->ordered()
            ->get();

        return HeroImageResource::collection($heroImages);
    }

    /**
     * Display all hero images (for admin purposes).
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function all()
    {
        $heroImages = HeroImage::ordered()->get();

        return HeroImageResource::collection($heroImages);
    }

    /**
     * Display the specified hero image.
     *
     * @param HeroImage $heroImage
     * @return HeroImageResource
     */
    public function show(HeroImage $heroImage)
    {
        return new HeroImageResource($heroImage);
    }

    /**
     * Get hero images statistics.
     *
     * @return JsonResponse
     */
    public function stats(): JsonResponse
    {
        $total = HeroImage::count();
        $active = HeroImage::active()->count();
        $inactive = $total - $active;

        return response()->json([
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
        ]);
    }
}
