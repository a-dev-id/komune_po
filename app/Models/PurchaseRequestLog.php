<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseRequestLog extends Model
{
    protected $fillable = [
        'purchase_request_id',
        'user_id',
        'role_name',
        'action',
        'from_status',
        'to_status',
        'from_step',
        'to_step',
        'remarks',
        'payload',
        'acted_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'acted_at' => 'datetime',
    ];

    public function purchaseRequest(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
