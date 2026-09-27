<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Student extends Model
{
    protected $table = 'students';

    public $timestamps = false;

    protected $fillable = [
        'class_id',
        'student_code',
        'full_name',
        'birth_year',
        'medical_info_encrypted',
    ];

    protected $casts = [
        'birth_year' => 'integer',
    ];

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(
            SchoolClass::class,
            'class_id',
            'id'
        );
    }
}
