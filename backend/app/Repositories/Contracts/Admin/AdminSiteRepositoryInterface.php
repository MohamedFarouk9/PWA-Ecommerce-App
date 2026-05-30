<?php

namespace App\Repositories\Contracts\Admin;

interface AdminSiteRepositoryInterface
{
    public function getContent(string $type);
    public function updateContent(string $type, array $data);
    public function getSettings();
    public function updateSettings(array $data);
    public function getSetting(string $key);
    public function updateSetting(string $key, $value);
}
