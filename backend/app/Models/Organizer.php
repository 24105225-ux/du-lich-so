<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organizer extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'organization_type',
        'license_no',
        'contact_phone',
        'contact_email',
        'address',
        'status',
    ];

    public function programs(): HasMany
    {
        return $this->hasMany(Program::class);
    }
}
