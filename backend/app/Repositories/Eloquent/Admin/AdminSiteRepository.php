<?php

namespace App\Repositories\Eloquent\Admin;

use App\Models\SiteContent;
use App\Models\SiteSetting;
use App\Repositories\Contracts\Admin\AdminSiteRepositoryInterface;
use Illuminate\Support\Str;

class AdminSiteRepository implements AdminSiteRepositoryInterface
{
    /**
     * Get content by type
     */
    public function getContent(string $type)
    {
        return SiteContent::where('type', $type)
            ->latest()
            ->first();
    }

    /**
     * Update content by type
     */
    public function updateContent(string $type, array $data)
    {
        return SiteContent::updateOrCreate(
            ['type' => $type],
            [
                'content' => $data['content'],
                'version' => Str::random(10)
            ]
        );
    }

    /**
     * Get all settings as array
     */
    public function getSettings()
    {
        return SiteSetting::pluck('value', 'key')->toArray();
    }

    /**
     * Update multiple settings
     */
    public function updateSettings(array $data)
    {
        $results = [];

        foreach ($data as $key => $value) {
            $results[$key] = $this->updateSetting($key, $value);
        }

        return $results;
    }

    /**
     * Get single setting
     */
    public function getSetting(string $key)
    {
        $setting = SiteSetting::where('key', $key)->first();
        return $setting ? $setting->value : null;
    }

    /**
     * Update single setting
     */
    public function updateSetting(string $key, $value)
    {
        return SiteSetting::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}
