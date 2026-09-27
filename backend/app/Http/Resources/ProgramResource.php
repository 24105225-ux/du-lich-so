<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProgramResource extends JsonResource
{
    public function toArray(
        Request $request
    ): array {
        return [
            'id' => $this->id,

            'code' => $this->code,

            'title' => $this->name,

            'education_level' =>
                $this->education_level,

            'price_per_student' =>
                (float)
                $this->base_cost_per_student,

            'duration_days' =>
                $this->duration_days,

            'capacity' =>
                $this->capacity,

            'status' =>
                $this->status,

            'description' =>
                $this->description,

            'organizer' =>
                $this->whenLoaded(
                    'organizer',
                    fn () => [
                        'id' =>
                            $this->organizer->id,

                        'name' =>
                            $this->organizer->name,
                    ]
                ),

            'schedules' =>
                $this->whenLoaded(
                    'schedules',
                    fn () =>
                        $this->schedules
                            ->map(
                                function ($schedule) {
                                    return [
                                        'id' =>
                                            $schedule->id,

                                        'trip_date' =>
                                            $schedule
                                                ->trip_date
                                                ->format(
                                                    'Y-m-d'
                                                ),

                                        'start_time' =>
                                            $schedule
                                                ->start_time,

                                        'end_time' =>
                                            $schedule
                                                ->end_time,

                                        'capacity' =>
                                            $schedule
                                                ->capacity,

                                        'status' =>
                                            $schedule
                                                ->status,

                                        'stops' =>
                                            $schedule
                                                ->stops
                                                ->map(
                                                    function (
                                                        $stop
                                                    ) {
                                                        return [
                                                            'day' =>
                                                                $stop
                                                                    ->day_no,

                                                            'sequence' =>
                                                                $stop
                                                                    ->seq_no,

                                                            'place' =>
                                                                $stop
                                                                    ->place
                                                                    ?->name,

                                                            'province' =>
                                                                $stop
                                                                    ->place
                                                                    ?->province,

                                                            'note' =>
                                                                $stop
                                                                    ->note,
                                                        ];
                                                    }
                                                )
                                                ->values(),
                                    ];
                                }
                            )
                            ->values()
                ),

            'links' => [
                'self' =>
                    route(
                        'programs.show',
                        $this->id
                    ),
            ],
        ];
    }
}



