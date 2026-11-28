<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;

class SiteContent extends Model
{
    protected $table = 'site_contents';

    protected $fillable = [
        'type',
        'content',
        'version',
    ];

    protected $casts = [
        'content' => 'string',
    ];

    public static function getLatestByType($type)
    {
        return static::where('type', $type)
            ->latest()
            ->first();
    }

    public function isUpdated($version)
    {
        return $this->version !== $version;
    }
}
