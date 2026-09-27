<?php

namespace App\Services;

use App\Models\Program;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProgramCatalogService
{
    public function search(
        array $filters,
        int $perPage = 9
    ): LengthAwarePaginator {
        $query = Program::query()
            ->published()
            ->with([
                'organizer:id,name',
            ])
            ->filter($filters);

        $sort = $filters['sort'] ?? 'newest';

        $query = match ($sort) {
            'price_asc' => $query->orderBy(
                'base_cost_per_student'
            ),

            'price_desc' => $query->orderByDesc(
                'base_cost_per_student'
            ),

            default => $query->orderByDesc(
                'created_at'
            ),
        };

        return $query
            ->paginate($perPage)
            ->withQueryString();
    }

    public function detail(Program $program): Program
    {
        return $program->load([
            'organizer:id,name',
            'schedules' => function ($query) {
                $query
                    ->where('status', 'open')
                    ->whereDate(
                        'trip_date',
                        '>=',
                        today()
                    )
                    ->with([
                        'stops.place',
                        'safetyProfile',
                    ]);
            },
        ]);
    }
}
