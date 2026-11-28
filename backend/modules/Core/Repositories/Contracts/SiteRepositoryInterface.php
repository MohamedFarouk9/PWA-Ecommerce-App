<?php

namespace Modules\Core\Repositories\Contracts;

interface SiteRepositoryInterface
{
    public function getContent(string $type);
    public function updateContent(string $type, array $data);
    public function getSettings();
    public function updateSettings(array $data);
    public function getSetting(string $key);
    public function updateSetting(string $key, $value);
}
