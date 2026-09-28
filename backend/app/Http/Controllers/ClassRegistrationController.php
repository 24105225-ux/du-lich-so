<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClassRegistrationRequest;
use App\Services\ClassRegistrationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use RuntimeException;

class ClassRegistrationController extends Controller
{
    public function __construct(
        private ClassRegistrationService $registrations
    ) {
    }

    public function create(): View
    {
        return view(
            'registrations.create',
            $this->registrations->formData()
        );
    }

    public function remainingSeats(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'class_id' => [
                'required',
                'integer',
                'exists:school_classes,id',
            ],
            'program_schedule_id' => [
                'required',
                'integer',
                'exists:program_schedules,id',
            ],
        ]);

        $remainingSeats = $this->registrations->remainingSeats(
            (int) $validated['class_id'],
            (int) $validated['program_schedule_id']
        );

        return response()->json([
            'remainingSeats' => $remainingSeats,
        ]);
    }
    public function store(
        StoreClassRegistrationRequest $request
    ) {
        try {
            $registration =
                $this->registrations->register(
                    (int) $request->class_id,
                    (int) $request->program_schedule_id,
                    (int) $request->student_count
                );
        } catch (RuntimeException $e) {
            Log::warning(
                'Dang ky lop that bai',
                [
                    'class_id' =>
                        $request->class_id,

                    'schedule_id' =>
                        $request->program_schedule_id,

                    'student_count' =>
                        $request->student_count,

                    'error' =>
                        $e->getMessage(),
                ]
            );

            return back()
                ->withInput()
                ->withErrors([
                    'student_count' =>
                        $e->getMessage(),
                ]);
        }

        return redirect()
            ->route(
                'registrations.success',
                $registration
            );
    }

    public function success(
        int $registration
    ): View {
        $item =
            \App\Models\ClassRegistration::query()
                ->with([
                    'schoolClass.school',
                    'schedule.program',
                ])
                ->findOrFail($registration);

        return view(
            'registrations.success',
            [
                'registration' => $item,
            ]
        );
    }
}

