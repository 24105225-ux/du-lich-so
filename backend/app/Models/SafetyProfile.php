<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SafetyProfile extends Model
{
    protected $table = 'safety_profiles';

    public $timestamps = false;

    protected $fillable = [
        'schedule_id',
        'risk_level',
        'first_aid_plan',
        'emergency_plan',
        'weather_threshold',
        'emergency_contact',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(
            ProgramSchedule::class,
            'schedule_id',
            'id'
        );
    }
}
