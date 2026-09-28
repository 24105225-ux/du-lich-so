<?php

namespace App\Services;

use App\Models\ClassRegistration;
use App\Models\ProgramSchedule;
use App\Models\SchoolClass;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ClassRegistrationService
{
    public function formData(): array
    {
        $classes = SchoolClass::query()
            ->with('school:id,name')
            ->withCount('students')
            ->orderBy('school_id')
            ->orderBy('class_name')
            ->get();

        $schedules = ProgramSchedule::query()
            ->with('program:id,name')
            ->where('status', 'open')
            ->whereDate('trip_date', '>=', today())
            ->orderBy('trip_date')
            ->orderBy('start_time')
            ->get();

        return [
            'classes' => $classes,
            'schedules' => $schedules,
        ];
    }

    public function remainingSeats(
        int $classId,
        int $scheduleId
    ): int {
        $schedule = ProgramSchedule::query()
            ->whereKey($scheduleId)
            ->firstOrFail();

        $used = ClassRegistration::query()
            ->where('class_id', $classId)
            ->where('schedule_id', $scheduleId)
            ->whereIn('status', ['pending', 'approved'])
            ->sum('student_count');

        return max(
            0,
            (int) $schedule->capacity - (int) $used
        );
    }

    public function register(
        int $classId,
        int $scheduleId,
        int $studentCount
    ): ClassRegistration {
        return DB::transaction(function () use (
            $classId,
            $scheduleId,
            $studentCount
        ) {
            $schedule = ProgramSchedule::query()
                ->whereKey($scheduleId)
                ->lockForUpdate()
                ->firstOrFail();

            SchoolClass::query()
                ->whereKey($classId)
                ->firstOrFail();

            if ($schedule->status !== 'open') {
                throw new RuntimeException(
                    'Lịch trình hiện không mở đăng ký.'
                );
            }

            if ($studentCount < 1) {
                throw new RuntimeException(
                    'Số học sinh đăng ký phải từ 1 trở lên.'
                );
            }

            $duplicate = ClassRegistration::query()
                ->where('class_id', $classId)
                ->where('schedule_id', $scheduleId)
                ->whereIn('status', ['pending', 'approved'])
                ->exists();

            if ($duplicate) {
                throw new RuntimeException(
                    'Lớp này đã đăng ký lịch trình này.'
                );
            }

            $used = ClassRegistration::query()
                ->where('class_id', $classId)
                ->where('schedule_id', $scheduleId)
                ->whereIn('status', ['pending', 'approved'])
                ->sum('student_count');

            $capacity = (int) $schedule->capacity;

            $remainingSeats = max(
                0,
                $capacity - (int) $used
            );

            if ($studentCount > $remainingSeats) {
                throw new RuntimeException(
                    "Chỉ còn {$remainingSeats} chỗ trống."
                );
            }

            return ClassRegistration::create([
                'class_id' => $classId,
                'schedule_id' => $scheduleId,
                'student_count' => $studentCount,
                'status' => 'pending',
            ]);
        });
    }
}