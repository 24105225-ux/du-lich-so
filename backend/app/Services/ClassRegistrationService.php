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
            ->with('program:id,name')
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

    public function remainingSeats(
        User $actor,
        int $classId,
        int $scheduleId
    ): int {
        $this->releaseExpiredHolds($scheduleId);

        $schedule = ProgramSchedule::query()
            ->whereKey($scheduleId)
            ->firstOrFail();

        $schoolClass = SchoolClass::query()
            ->with('school')
            ->whereKey($classId)
            ->firstOrFail();

        if (
            $actor->role === 'school' &&
            $schoolClass->school?->user_id !== $actor->id
        ) {
            throw new RuntimeException(
                'Bạn không có quyền sử dụng lớp học này.'
            );
        }

        $usedSeats = ClassRegistration::query()
            ->where('class_id', $schoolClass->id)
            ->where('schedule_id', $schedule->id)
            ->whereIn('status', ['pending', 'approved'])
            ->sum('student_count');

        return max(
            0,
            (int) $schedule->capacity - (int) $usedSeats
        );
    }
    /**
     * Giai phong cac luot giu cho pending da het han.
     */
    public function releaseExpiredHolds(?int $scheduleId = null): int
    {
        $query = ClassRegistration::query()
            ->where('status', 'pending')
            ->whereNotNull('hold_expires_at')
            ->where('hold_expires_at', '<=', now());

        if ($scheduleId !== null) {
            $query->where('schedule_id', $scheduleId);
        }

        return $query->update([
            'status' => 'cancelled',
        ]);
    }
    public function register(
        $user,
        int $classId,
        int $scheduleId,
        int $studentCount
    )
    {
        return DB::transaction(function () use (
            $user,
            $classId,
            $scheduleId,
            $studentCount
        ) {

            /*
             * Khoa schedule truoc.
             *
             * Moi luot booking cung schedule se di qua
             * cung row lock, do do tranh duoc:
             * - overselling
             * - hai user cung dat ghe cuoi
             */
            $schedule = ProgramSchedule::query()
                ->lockForUpdate()
                ->findOrFail($scheduleId);

            /*
             * Chi giai phong hold cua schedule hien tai.
             */
            ClassRegistration::query()
                ->where('schedule_id', $scheduleId)
                ->where('status', 'pending')
                ->whereNotNull('hold_expires_at')
                ->where('hold_expires_at', '<=', now())
                ->update([
                    'status' => 'cancelled',
                ]);

            /*
             * Tim registration hien co cua cung class + schedule.
             *
             * Vi DB van giu unique uq_class_schedule,
             * ta REUSE row cancelled thay vi insert row moi.
             */
            $existing = ClassRegistration::query()
                ->where('class_id', $classId)
                ->where('schedule_id', $scheduleId)
                ->lockForUpdate()
                ->first();

            if ($existing) {

                /*
                 * Pending nhung da het han:
                 * doi sang cancelled de co the reuse.
                 */
                if (
                    $existing->status === 'pending' &&
                    $existing->hold_expires_at &&
                    $existing->hold_expires_at->lte(now())
                ) {
                    $existing->status = 'cancelled';
                    $existing->save();
                }

                /*
                 * Pending con han / approved / completed:
                 * khong cho dat trung.
                 */
                if (
                    in_array(
                        $existing->status,
                        ['pending', 'approved', 'completed'],
                        true
                    )
                ) {
                    throw new RuntimeException(
                        'Lớp này đã có đăng ký đang hoạt động cho lịch đã chọn.'
                    );
                }
            }

            /*
             * student_count phai > 0.
             */
            if ($studentCount < 1) {
                throw new RuntimeException(
                    'Số lượng học sinh phải lớn hơn 0.'
                );
            }

            /*
             * Tinh so ghe dang su dung.
             */
            $usedSeats = (int) ClassRegistration::query()
                ->where('schedule_id', $scheduleId)
                ->whereIn('status', ['pending', 'approved'])
                ->sum('student_count');

            $capacity = (int) $schedule->capacity;
            $remainingSeats = max(0, $capacity - $usedSeats);

            if ($studentCount > $remainingSeats) {
                throw new RuntimeException(
                    "Không đủ chỗ. Chỉ còn {$remainingSeats} chỗ."
                );
            }

            /*
             * Tao moi hoac reuse row cancelled.
             */
            if ($existing) {

                $existing->fill([
                    'student_count' => $studentCount,
                    'status' => 'pending',
                    'hold_expires_at' => now()->addMinutes(15),
                    'registered_at' => now(),
                ]);

                $existing->save();

                return $existing->fresh();
            }

            return ClassRegistration::query()->create([
                'class_id' => $classId,
                'schedule_id' => $scheduleId,
                'student_count' => $studentCount,
                'status' => 'pending',
                'hold_expires_at' => now()->addMinutes(15),
                'registered_at' => now(),
            ]);
        });
    }
}
