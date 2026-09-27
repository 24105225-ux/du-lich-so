<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolClass extends Model
{
    protected $table = 'school_classes';

    protected $fillable = [
        'school_id',
        'class_code',
        'class_name',
        'grade_level',
        'academic_year',
        'homeroom_teacher_id',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(
            Student::class,
            'class_id'
        );
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(
            ClassRegistration::class,
            'class_id'
        );
    }

    public function getStudentCountAttribute(): int
    {
        return $this->students()->count();
    }
}
