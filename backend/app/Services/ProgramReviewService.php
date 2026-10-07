<?php

namespace App\Services;

use App\Models\ProgramReview;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ProgramReviewService
{
    public function eligibility(User $user, int $programId): array
    {
        $parent = DB::table('parents')->where('user_id', $user->id)->first();
        $eligible = false;

        if ($parent) {
            $studentIds = DB::table('parent_student')
                ->where('parent_id', $parent->id)
                ->pluck('student_id');

            $eligible = DB::table('attendance_records as ar')
                ->join('attendance_points as ap', 'ap.id', '=', 'ar.attendance_point_id')
                ->join('program_schedules as ps', 'ps.id', '=', 'ap.schedule_id')
                ->where('ps.program_id', $programId)
                ->whereIn('ar.student_id', $studentIds)
                ->whereIn('ar.attendance_status', ['present', 'late'])
                ->exists();
        }

        $reviewed = ProgramReview::query()
            ->where('program_id', $programId)
            ->where('user_id', $user->id)
            ->exists();

        return ['eligible' => $eligible, 'reviewed' => $reviewed];
    }

    public function create(User $user, int $programId, int $rating, ?string $comment): ProgramReview
    {
        $eligibility = $this->eligibility($user, $programId);

        if (!$eligibility['eligible']) {
            throw new RuntimeException('Chỉ người đã thực sự tham gia chuyến đi mới được đánh giá.');
        }

        if ($eligibility['reviewed']) {
            throw new RuntimeException('Bạn đã đánh giá chương trình này.');
        }

        return DB::transaction(function () use ($user, $programId, $rating, $comment) {
            return ProgramReview::query()->create([
                'program_id' => $programId,
                'user_id' => $user->id,
                'rating' => $rating,
                'comment' => $comment,
            ]);
        });
    }
}
