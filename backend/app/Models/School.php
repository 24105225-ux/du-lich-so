<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class School extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id', 'name', 'province', 'address', 'status',
    ];

    public function user(): BelongsTo
    { return $this->belongsTo(User::class); }

    public function classes(): HasMany
    { return $this->hasMany(SchoolClass::class); }
}
