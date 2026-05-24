<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseRequestVendorOffer extends Model
{
    protected $fillable = [
        'purchase_request_id',
        'vendor_id',

        'vendor_name_snapshot',
        'vendor_phone_snapshot',
        'vendor_email_snapshot',
        'vendor_address_snapshot',

        'quotation_number',
        'quotation_file',

        'currency',
        'offer_total',
        'lead_time_days',
        'notes',

        'is_selected_by_cost_control',
        'selected_by',
        'selected_at',

        'created_by',
    ];

    protected $casts = [
        'offer_total' => 'decimal:2',
        'lead_time_days' => 'integer',
        'is_selected_by_cost_control' => 'boolean',
        'selected_at' => 'datetime',
    ];

    public function purchaseRequest(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseRequestVendorOfferItem::class, 'purchase_request_vendor_offer_id')
            ->orderBy('purchase_request_item_id')
            ->orderBy('id');
    }

    public function offerItems(): HasMany
    {
        return $this->hasMany(PurchaseRequestVendorOfferItem::class, 'purchase_request_vendor_offer_id')
            ->orderBy('purchase_request_item_id')
            ->orderBy('id');
    }

    public function selectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'selected_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
