<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'class_registration_id',
        'order_no',
        'amount',
        'status',
        'paid_at',
        'cancelled_at',
        'refunded_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(
            ClassRegistration::class,
            'class_registration_id'
        );
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function canTransitionTo(string $next): bool
    {
        $allowed = [
            'pending' => ['paid', 'cancelled'],
            'paid' => ['refunded'],
            'cancelled' => [],
            'refunded' => [],
        ];

        return in_array(
            $next,
            $allowed[$this->status] ?? [],
            true
        );
    }
}