<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'actor_id', 'action', 'entity', 'entity_id',
        'before_json', 'after_json', 'ip_address', 'created_at',
    ];

    public function actor(): BelongsTo
    { return $this->belongsTo(User::class, 'actor_id'); }
}
