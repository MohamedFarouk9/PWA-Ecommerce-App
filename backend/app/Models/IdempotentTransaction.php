<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class IdempotentTransaction extends Model
{
    protected $table = 'idempotent_transactions';

    protected $fillable = [
        'idempotency_key',
        'gateway',
        'order_id',
        'customer_id',
        'request_hash',
        'transaction_id',
        'status',
        'response',
        'processed_at',
    ];

    protected $casts = [
        'response' => 'json',
        'processed_at' => 'datetime',
    ];

    public $timestamps = true;

    /**
     * Scope: Find by idempotency key
     */
    public function scopeByKey(Builder $query, string $key)
    {
        return $query->where('idempotency_key', $key);
    }

    /**
     * Scope: Recent (within last 24 hours)
     */
    public function scopeRecent(Builder $query)
    {
        return $query->where('created_at', '>=', now()->subDay());
    }
}
