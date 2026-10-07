<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleStop extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'schedule_id', 'place_id', 'seq_no', 'activity', 'duration_minutes',
    ];

    protected $casts = [
        'seq_no' => 'integer',
        'duration_minutes' => 'integer',
    ];

    public function schedule(): BelongsTo
    { return $this->belongsTo(ProgramSchedule::class, 'schedule_id'); }

    public function place(): BelongsTo
    { return $this->belongsTo(Place::class); }
}
