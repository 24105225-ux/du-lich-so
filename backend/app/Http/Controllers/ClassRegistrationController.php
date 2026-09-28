<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClassRegistrationRequest;
use App\Models\ClassRegistration;
use App\Services\ClassRegistrationService;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Gate;
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
            $this->registrations->formData(
                request()->user()
            )
        );
    }

    public function store(
        StoreClassRegistrationRequest $request
    ): RedirectResponse {
        try {
            $registration =
                $this->registrations->register(
                    $request->user(),
                    (int) $request->class_id,
                    (int) $request->program_schedule_id,
                    (int) $request->student_count,
                    $request->note
                );
        } catch (RuntimeException $e) {
            Log::warning(
                'Dang ky lop that bai',
                [
                    'user_id' =>
                        $request->user()->id,

                    'class_id' =>
                        $request->class_id,

                    'schedule_id' =>
                        $request
                            ->program_schedule_id,

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

        $token = Crypt::encryptString(
            (string) $registration->id
        );

        return redirect()
            ->route(
                'registrations.success',
                ['token' => $token]
            )
            ->with(
                'status',
                'Đăng ký đã được ghi nhận.'
            );
    }

    public function success(
        string $token
    ): View {
        try {
            $id = (int) Crypt::decryptString(
                $token
            );
        } catch (
            DecryptException
        ) {
            abort(404);
        }

        $registration =
            ClassRegistration::query()
                ->with([
                    'schoolClass.school',
                    'schedule.program',
                ])
                ->findOrFail($id);

        Gate::authorize(
            'view',
            $registration
        );

        return view(
            'registrations.success',
            [
                'registration' =>
                    $registration,
            ]
        );
    }
}
