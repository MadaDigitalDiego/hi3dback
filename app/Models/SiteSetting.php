<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'about_title',
        'about_subtitle',
        'about_story',
        'about_mission',
        'about_values',
        'about_team',
        'about_cta_title',
        'about_cta_description',
        'social_facebook',
        'social_twitter',
        'social_instagram',
        'social_linkedin',
        'social_youtube',
        'social_tiktok',
    ];

    protected $casts = [
        'about_values' => 'array',
        'about_team' => 'array',
    ];

    public static function getSettings(): ?self
    {
        return self::first();
    }

    public static function getSocialLinks(): array
    {
        $settings = self::first();
        if (!$settings) {
            return [];
        }

        return array_filter([
            'facebook' => $settings->social_facebook,
            'twitter' => $settings->social_twitter,
            'instagram' => $settings->social_instagram,
            'linkedin' => $settings->social_linkedin,
            'youtube' => $settings->social_youtube,
            'tiktok' => $settings->social_tiktok,
        ]);
    }

    public static function getAboutContent(): array
    {
        $settings = self::first();
        if (!$settings) {
            return [];
        }

        return [
            'title' => $settings->about_title,
            'subtitle' => $settings->about_subtitle,
            'story' => $settings->about_story,
            'mission' => $settings->about_mission,
            'values' => $settings->about_values ?? [],
            'team' => $settings->about_team ?? [],
            'cta_title' => $settings->about_cta_title,
            'cta_description' => $settings->about_cta_description,
        ];
    }
}
