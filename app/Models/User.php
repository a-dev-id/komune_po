<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'department_name',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function purchaseRequests(): HasMany
    {
        return $this->hasMany(PurchaseRequest::class, 'requested_by');
    }

    public function purchaseRequestLogs(): HasMany
    {
        return $this->hasMany(PurchaseRequestLog::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isRequester(): bool
    {
        return $this->role === 'requester';
    }

    public function isPurchasing(): bool
    {
        return $this->role === 'purchasing';
    }

    public function isCostControl(): bool
    {
        return $this->role === 'cost_control';
    }

    public function isGm(): bool
    {
        return $this->role === 'gm';
    }

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function isFinancialController(): bool
    {
        return $this->role === 'financial_controller';
    }
}
