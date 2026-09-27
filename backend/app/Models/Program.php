<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Program extends Model
{
    protected $fillable = [
        'organizer_id',
        'code',
        'name',
        'education_level',
        'base_cost_per_student',
        'duration_days',
        'capacity',
        'status',
        'description',
    ];

    protected $casts = [
        'base_cost_per_student' => 'decimal:2',
        'duration_days' => 'integer',
        'capacity' => 'integer',
    ];

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(Organizer::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(ProgramSchedule::class)
            ->orderBy('trip_date')
            ->orderBy('start_time');
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(
            Subject::class,
            'program_subjects'
        );
    }

    public function requirements(): BelongsToMany
    {
        return $this->belongsToMany(
            EducationalRequirement::class,
            'program_requirements'
        );
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopeFilter(
        Builder $query,
        array $filters
    ): Builder {
        return $query
            ->when(
                $filters['keyword'] ?? null,
                function (Builder $q, string $keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                }
            )
            ->when(
                $filters['education_level'] ?? null,
                function (Builder $q, string $level) {
                    $q->where('education_level', $level);
                }
            )
            ->when(
                $filters['min_price'] ?? null,
                function (Builder $q, $price) {
                    $q->where(
                        'base_cost_per_student',
                        '>=',
                        $price
                    );
                }
            )
            ->when(
                $filters['max_price'] ?? null,
                function (Builder $q, $price) {
                    $q->where(
                        'base_cost_per_student',
                        '<=',
                        $price
                    );
                }
            );
    }
}


