<?php

namespace Modules\Marketing\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationTemplate extends Model
{
    use HasFactory;

    protected $table = 'notification_templates';

    protected $fillable = [
        'name',
        'subject',
        'body',
        'type',
        'variables',
    ];

    protected $casts = [
        'variables' => 'json',
    ];

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'type', 'type');
    }

    public function render(array $data = [])
    {
        $subject = $this->subject;
        $body    = $this->body;

        foreach ($data as $key => $value) {
            $placeholder = '{{' . $key . '}}';
            $subject     = str_replace($placeholder, $value, $subject);
            $body        = str_replace($placeholder, $value, $body);
        }

        return [
            'subject' => $subject,
            'body'    => $body,
        ];
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }
}
