<?php

namespace App\Services;

use App\Models\ClassRegistration;
use App\Models\ProgramSchedule;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ClassRegistrationService
{
    public function formData(User $actor): array
    {
        $classQuery = SchoolClass::query()
            ->with('school:id,name')
            ->orderBy('school_id')
            ->orderBy('class_name');

        if ($actor->role === 'school') {
            $classQuery->whereHas(
                'school',
                function ($query) use ($actor) {
                    $query->where(
                        'user_id',
                        $actor->id
                    );
                }
            );
        }

        $classes = $classQuery->get();

        $schedules = ProgramSchedule::query()
            ->with('program:id,title')
            ->where(
                'status',
                'open'
            )
            ->whereDate(
                'trip_date',
                '>=',
                today()
            )
            ->orderBy('trip_date')
            ->orderBy('start_time')
            ->get();

        return [
            'classes' => $classes,
            'schedules' => $schedules,
        ];
    }

    public function register(
        User $actor,
        int $classId,
        int $scheduleId,
        int $studentCount,
        ?string $note = null
    ): ClassRegistration {
        return DB::transaction(
            function () use (
                $actor,
                $classId,
                $scheduleId,
                $studentCount,
                $note
            ) {
                $schedule =
                    ProgramSchedule::query()
                        ->whereKey($scheduleId)
                        ->lockForUpdate()
                        ->firstOrFail();

                $schoolClass =
                    SchoolClass::query()
                        ->with('school')
                        ->whereKey($classId)
                        ->firstOrFail();

                if (
                    $actor->role === 'school' &&
                    $schoolClass->school?->user_id
                        !== $actor->id
                ) {
                    throw new RuntimeException(
                        'Bạn không có quyền sử dụng lớp học này.'
                    );
                }

                if (
                    $schedule->status !== 'open'
                ) {
                    throw new RuntimeException(
                        'Chương trình hiện không mở đăng ký.'
                    );
                }

                if (
                    $studentCount >
                    $schoolClass->student_count
                ) {
                    throw new RuntimeException(
                        'Số học sinh đăng ký không được lớn hơn sĩ số lớp.'
                    );
                }

                $duplicate =
                    ClassRegistration::query()
                        ->where(
                            'class_id',
                            $schoolClass->id
                        )
                        ->where(
                            'program_schedule_id',
                            $schedule->id
                        )
                        ->whereIn(
                            'status',
                            ['pending', 'approved']
                        )
                        ->exists();

                if ($duplicate) {
                    throw new RuntimeException(
                        'Lớp này đã đăng ký chương trình này.'
                    );
                }

                $usedSeats =
                    ClassRegistration::query()
                        ->where(
                            'class_id',
                            $schoolClass->id
                        )
                        ->where(
                            'program_schedule_id',
                            $schedule->id
                        )
                        ->whereIn(
                            'status',
                            ['pending', 'approved']
                        )
                        ->sum('student_count');

                $remainingSeats =
                    (int) $schedule->capacity
                    - (int) $usedSeats;

                if (
                    $studentCount >
                    $remainingSeats
                ) {
                    throw new RuntimeException(
                        "Chỉ còn {$remainingSeats} chỗ trống."
                    );
                }

                return ClassRegistration::create([
                    'class_id' =>
                        $schoolClass->id,

                    'program_schedule_id' =>
                        $schedule->id,

                    'student_count' =>
                        $studentCount,

                    'status' =>
                        'pending',

                    'note' =>
                        $note,
                ]);
            }
        );
    }
}
