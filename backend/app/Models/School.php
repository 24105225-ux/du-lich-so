<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class School extends Model
{
    protected $fillable = [
        'user_id',
        'school_code',
        'name',
        'education_level',
        'address',
        'district',
        'province',
        'contact_phone',
        'contact_email',
    ];

    public function classes(): HasMany
    {
        return $this->hasMany(SchoolClass::class);
    }
}
