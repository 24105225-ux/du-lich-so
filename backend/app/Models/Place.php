<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Place extends Model
{
    protected $fillable = [
        'code',
        'name',
        'province',
        'place_type',
        'latitude',
        'longitude',
        'source_note',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function scheduleStops(): HasMany
    {
        return $this->hasMany(ScheduleStop::class);
    }
}
