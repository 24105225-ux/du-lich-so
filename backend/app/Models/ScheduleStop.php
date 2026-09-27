<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleStop extends Model
{
    protected $fillable = [
        'schedule_id',
        'place_id',
        'seq_no',
        'activity',
        'duration_minutes',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(
            ProgramSchedule::class,
            'schedule_id',
            'id'
        );
    }

    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class);
    }
}
