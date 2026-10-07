<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Place extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'name', 'province', 'lat', 'lng', 'best_season',
        'visit_minutes', 'description', 'source_note',
    ];

    protected $casts = [
        'lat' => 'decimal:7',
        'lng' => 'decimal:7',
        'visit_minutes' => 'integer',
    ];

    public function scheduleStops(): HasMany
    { return $this->hasMany(ScheduleStop::class, 'place_id'); }
}
