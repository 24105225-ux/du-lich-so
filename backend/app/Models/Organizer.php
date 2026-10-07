<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organizer extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id', 'name', 'license_code', 'province', 'status',
    ];

    public function user(): BelongsTo
    { return $this->belongsTo(User::class); }

    public function programs(): HasMany
    { return $this->hasMany(Program::class); }
}
