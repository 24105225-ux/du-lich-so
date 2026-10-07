<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgramReview extends Model
{
    protected $fillable = ['program_id', 'user_id', 'rating', 'comment'];

    protected $casts = ['rating' => 'integer'];

    public function program(): BelongsTo
    { return $this->belongsTo(Program::class); }

    public function user(): BelongsTo
    { return $this->belongsTo(User::class); }
}
