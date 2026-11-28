<?php

namespace Modules\Marketing\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationSettings extends Model
{
    use HasFactory;

    protected $table = 'notification_settings';

    protected $fillable = [
        'user_id',
        'email_enabled',
        'sms_enabled',
        'realtime_enabled',
        'notification_types',
    ];

    protected $casts = [
        'email_enabled'      => 'boolean',
        'sms_enabled'        => 'boolean',
        'realtime_enabled'   => 'boolean',
        'notification_types' => 'json',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isTypeEnabled($type)
    {
        $types = $this->notification_types ?? [];
        return $types[$type] ?? false;
    }

    public function enableType($type)
    {
        $types        = $this->notification_types ?? [];
        $types[$type] = true;
        $this->update(['notification_types' => $types]);
    }

    public function disableType($type)
    {
        $types        = $this->notification_types ?? [];
        $types[$type] = false;
        $this->update(['notification_types' => $types]);
    }

    public function enableAll()
    {
        $this->update([
            'email_enabled'    => true,
            'sms_enabled'      => true,
            'realtime_enabled' => true,
        ]);
    }

    public function disableAll()
    {
        $this->update([
            'email_enabled'    => false,
            'sms_enabled'      => false,
            'realtime_enabled' => false,
        ]);
    }
}
