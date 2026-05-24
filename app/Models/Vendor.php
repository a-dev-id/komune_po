<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendor extends Model
{
    protected $fillable = [
        'name',
        'normalized_name',
        'category',
        'contact_person',
        'phone',
        'email',
        'address',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (Vendor $vendor) {
            $vendor->normalized_name = self::normalizeName($vendor->name);
        });
    }

    public static function normalizeName(?string $name): string
    {
        $name = strtolower(trim((string) $name));

        $name = preg_replace('/\s+/', ' ', $name);
        $name = str_replace(['.', ',', '-', '_'], '', $name);

        return trim($name);
    }

    public function purchaseRequestVendorOffers(): HasMany
    {
        return $this->hasMany(PurchaseRequestVendorOffer::class);
    }
}
