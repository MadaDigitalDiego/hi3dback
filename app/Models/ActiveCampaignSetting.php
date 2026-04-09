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

    protected $hidden = [
        'api_key',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'mapping' => 'array',
    ];

    protected static function booted()
    {
        // Ensure only one configuration is active at a time.
        static::saving(function (self $model) {
            if ($model->is_enabled) {
                // disable other active configs
                self::where('id', '<>', $model->id ?? 0)->where('is_enabled', true)->update(['is_enabled' => false]);
            }
        });
    }

    public function setApiKeyAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['api_key'] = null;
            return;
        }

        // Always encrypt API key at rest
        try {
            $this->attributes['api_key'] = encrypt($value);
        } catch (\Exception $e) {
            // fallback to storing as-is if encryption fails for some reason
            $this->attributes['api_key'] = $value;
        }
    }

    public function getApiKeyAttribute($value)
    {
        if (empty($value)) {
            return null;
        }

        try {
            return decrypt($value);
        } catch (\Exception $e) {
            // value not encrypted, return raw
            return $value;
        }
    }

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
        $map = $config?->mapping ?? [];

        return [
            'tags' => $map['tags'] ?? [],
            'lists' => $map['lists'] ?? [],
            'automations' => $map['automations'] ?? [],
        ];
    }

    public function isConfigured(): bool
    {
        return filled($this->api_url) && filled($this->api_key);
    }
}
