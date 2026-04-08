<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActiveCampaignSetting extends Model
{
    use SoftDeletes;

    protected $table = 'active_campaign_settings';

    protected $fillable = [
        'api_url',
        'api_key',
        'is_enabled',
        'mapping',
        'description',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'mapping' => 'array',
    ];

    protected $hidden = [
        'api_key',
    ];

    public static function getActive(): ?self
    {
        return self::where('is_enabled', true)
            ->whereNull('deleted_at')
            ->first();
    }

    public static function getActiveWithKey(): ?self
    {
        return self::where('is_enabled', true)
            ->whereNull('deleted_at')
            ->first();
    }

    public static function getApiUrl(): ?string
    {
        $config = self::getActive();
        return $config?->api_url;
    }

    public static function getApiKey(): ?string
    {
        $config = self::getActiveWithKey();
        return $config?->api_key;
    }

    public static function getMapping(): array
    {
        $config = self::getActive();
        return $config?->mapping ?? [];
    }

    public function isConfigured(): bool
    {
        return filled($this->api_url) && filled($this->api_key);
    }
}
