<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassRegistration extends Model
{
    protected $table = 'class_registrations';

    /*
     * Bảng class_registrations không có created_at / updated_at.
     */
    public $timestamps = false;

    protected $fillable = [
        'class_id',
        'schedule_id',
        'student_count',
        'status',
        'hold_expires_at',
    ];

    protected $casts = [
        'student_count' => 'integer',
        'registered_at' => 'datetime',
        'hold_expires_at' => 'datetime',
    ];

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(
            SchoolClass::class,
            'class_id'
        );
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(
            ProgramSchedule::class,
            'schedule_id'
        );
    }
}
