<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseRequestVendorOfferItem extends Model
{
    protected $fillable = [
        'purchase_request_vendor_offer_id',
        'purchase_request_item_id',
        'unit_price',
        'quantity',
        'total_price',
        'brand',
        'notes',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'quantity' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saving(function (PurchaseRequestVendorOfferItem $item) {
            $quantity = (float) ($item->quantity ?? 0);
            $unitPrice = (float) ($item->unit_price ?? 0);

            $item->total_price = $quantity * $unitPrice;
        });
    }

    public function vendorOffer(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequestVendorOffer::class, 'purchase_request_vendor_offer_id');
    }

    public function purchaseRequestItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequestItem::class);
    }
}
