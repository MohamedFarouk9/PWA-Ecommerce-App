<?php

namespace Modules\Core\Repositories\Eloquent;

use Illuminate\Support\Str;
use Modules\Core\Models\SiteContent;
use Modules\Core\Models\SiteSetting;
use Modules\Core\Repositories\Contracts\SiteRepositoryInterface;

class SiteRepository implements SiteRepositoryInterface
{
    public function getContent(string $type)
    {
        return SiteContent::where('type', $type)
            ->latest()
            ->first();
    }

    public function updateContent(string $type, array $data)
    {
        return SiteContent::updateOrCreate(
            ['type' => $type],
            [
                'content' => $data['content'],
                'version' => Str::random(10),
            ]
        );
    }

    public function getSettings()
    {
        return SiteSetting::pluck('value', 'key')->toArray();
    }

    public function updateSettings(array $data)
    {
        $results = [];

        foreach ($data as $key => $value) {
            $results[$key] = $this->updateSetting($key, $value);
        }

        return $results;
    }

    public function getSetting(string $key)
    {
        $setting = SiteSetting::where('key', $key)->first();
        return $setting ? $setting->value : null;
    }

    public function updateSetting(string $key, $value)
    {
        return SiteSetting::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}
