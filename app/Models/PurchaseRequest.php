<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseRequest extends Model
{
    protected $fillable = [
        'pr_number',
        'title',
        'requested_by',
        'requester_name',
        'department_name',
        'date_needed',
        'status',
        'current_step',

        'requester_remarks',
        'purchasing_remarks',
        'cost_control_remarks',
        'gm_remarks',
        'owner_remarks',
        'financial_controller_remarks',

        'hold_until',
        'hold_expired_at',

        'rejected_by',
        'rejected_at',
        'rejection_reason',

        'split_from_id',
        'split_from_pr_number',

        'received_by_name',
        'received_date',
        'received_at',
        'received_remarks',

        'handover_date',
        'handed_over_at',
        'handover_remarks',

        'completed_at',

        'submitted_at',
        'current_status_at',
    ];

    protected $casts = [
        'date_needed' => 'date',

        'hold_until' => 'datetime',
        'hold_expired_at' => 'datetime',

        'rejected_at' => 'datetime',

        'received_date' => 'date',
        'received_at' => 'datetime',

        'handover_date' => 'date',
        'handed_over_at' => 'datetime',

        'completed_at' => 'datetime',

        'submitted_at' => 'datetime',
        'current_status_at' => 'datetime',
    ];

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseRequestItem::class)
            ->orderBy('sort_order');
    }

    public function vendorOffers(): HasMany
    {
        return $this->hasMany(PurchaseRequestVendorOffer::class)
            ->latest('id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(PurchaseRequestLog::class)
            ->latest('acted_at')
            ->latest('id');
    }

    public function splitFrom(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class, 'split_from_id');
    }

    public function splitChildren(): HasMany
    {
        return $this->hasMany(PurchaseRequest::class, 'split_from_id');
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isWithRequester(): bool
    {
        return $this->current_step === 'requester';
    }

    public function isWithPurchasing(): bool
    {
        return $this->current_step === 'purchasing';
    }

    public function isWithCostControl(): bool
    {
        return $this->current_step === 'cost_control';
    }

    public function isWithGm(): bool
    {
        return $this->current_step === 'gm';
    }

    public function isWithOwner(): bool
    {
        return $this->current_step === 'owner';
    }

    public function isWithFinancialController(): bool
    {
        return $this->current_step === 'financial_controller';
    }

    public function isCompleted(): bool
    {
        return $this->current_step === 'completed';
    }
}
