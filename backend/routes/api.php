<?php

use App\Http\Controllers\Api\ProgramApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    Route::get(
        '/programs',
        [ProgramApiController::class, 'index']
    );

    Route::get(
        '/programs/{program}/recommendations',
        function (int $program) {
            $current = DB::table('programs')
                ->where('id', $program)
                ->first();

            if (!$current) {
                abort(404);
            }

            $candidates = DB::table('programs')
                ->where('id', '<>', $program)
                ->select(
                    'id',
                    'name as title',
                    'education_level',
                    'base_cost_per_student as price_per_student'
                )
                ->limit(30)
                ->get();

            $payload = json_encode([
                'current' => (array) $current,
                'candidates' => $candidates
                    ->map(fn ($item) => (array) $item)
                    ->values()
                    ->all(),
            ], JSON_UNESCAPED_UNICODE);

            $script = base_path(
                '../python-service/recommend.py'
            );

            $pipes = [];

            $process = proc_open(
                'python ' . escapeshellarg($script),
                [
                    0 => ['pipe', 'r'],
                    1 => ['pipe', 'w'],
                    2 => ['pipe', 'w'],
                ],
                $pipes
            );

            if (!is_resource($process)) {
                return response()->json([
                    'data' => [],
                ]);
            }

            fwrite($pipes[0], $payload);
            fclose($pipes[0]);

            $output = stream_get_contents($pipes[1]);
            fclose($pipes[1]);

            fclose($pipes[2]);

            proc_close($process);

            $data = json_decode(
                trim($output),
                true
            );

            return response()->json([
                'data' => is_array($data)
                    ? $data
                    : [],
            ]);
        }
    );

    Route::middleware(['web', 'auth'])->group(function () {

        Route::get(
            '/me',
            function (Request $request) {
                $user = $request->user();

                return response()->json([
                    'data' => [
                        'id' => $user->id,
                        'email' => $user->email,
                        'role' => $user->role,
                    ],
                ]);
            }
        );

        Route::get(
            '/programs/{program}/review-eligibility',
            function (
                Request $request,
                int $program
            ) {
                $parent = DB::table('parents')
                    ->where(
                        'user_id',
                        $request->user()->id
                    )
                    ->first();

                $eligible = false;

                if ($parent) {
                    $studentIds = DB::table(
                        'parent_student'
                    )
                        ->where(
                            'parent_id',
                            $parent->id
                        )
                        ->pluck('student_id');

                    $eligible = DB::table(
                        'attendance_records as ar'
                    )
                        ->join(
                            'attendance_points as ap',
                            'ap.id',
                            '=',
                            'ar.attendance_point_id'
                        )
                        ->join(
                            'program_schedules as ps',
                            'ps.id',
                            '=',
                            'ap.schedule_id'
                        )
                        ->where(
                            'ps.program_id',
                            $program
                        )
                        ->whereIn(
                            'ar.student_id',
                            $studentIds
                        )
                        ->whereIn(
                            'ar.attendance_status',
                            ['present', 'late']
                        )
                        ->exists();
                }

                $reviewed = DB::table(
                    'program_reviews'
                )
                    ->where(
                        'program_id',
                        $program
                    )
                    ->where(
                        'user_id',
                        $request->user()->id
                    )
                    ->exists();

                return response()->json([
                    'eligible' => $eligible,
                    'reviewed' => $reviewed,
                ]);
            }
        );

        Route::post(
            '/programs/{program}/reviews',
            function (
                Request $request,
                int $program
            ) {
                $request->validate([
                    'rating' => [
                        'required',
                        'integer',
                        'between:1,5',
                    ],
                    'comment' => [
                        'nullable',
                        'string',
                        'max:2000',
                    ],
                ]);

                $parent = DB::table('parents')
                    ->where(
                        'user_id',
                        $request->user()->id
                    )
                    ->first();

                abort_unless(
                    $parent,
                    403,
                    'Chỉ phụ huynh có học sinh đã tham gia mới được đánh giá.'
                );

                $studentIds = DB::table(
                    'parent_student'
                )
                    ->where(
                        'parent_id',
                        $parent->id
                    )
                    ->pluck('student_id');

                $eligible = DB::table(
                    'attendance_records as ar'
                )
                    ->join(
                        'attendance_points as ap',
                        'ap.id',
                        '=',
                        'ar.attendance_point_id'
                    )
                    ->join(
                        'program_schedules as ps',
                        'ps.id',
                        '=',
                        'ap.schedule_id'
                    )
                    ->where(
                        'ps.program_id',
                        $program
                    )
                    ->whereIn(
                        'ar.student_id',
                        $studentIds
                    )
                    ->whereIn(
                        'ar.attendance_status',
                        ['present', 'late']
                    )
                    ->exists();

                abort_unless(
                    $eligible,
                    403,
                    'Chỉ người đã thực sự tham gia chuyến đi mới được đánh giá.'
                );

                $exists = DB::table(
                    'program_reviews'
                )
                    ->where(
                        'program_id',
                        $program
                    )
                    ->where(
                        'user_id',
                        $request->user()->id
                    )
                    ->exists();

                if ($exists) {
                    return response()->json([
                        'message' =>
                            'Bạn đã đánh giá chương trình này.',
                    ], 409);
                }

                $id = DB::table(
                    'program_reviews'
                )->insertGetId([
                    'program_id' =>
                        $program,
                    'user_id' =>
                        $request->user()->id,
                    'rating' =>
                        $request->integer('rating'),
                    'comment' =>
                        $request->input('comment'),
                    'created_at' =>
                        now(),
                    'updated_at' =>
                        now(),
                ]);

                return response()->json([
                    'data' => [
                        'id' => $id,
                    ],
                    'message' =>
                        'Đánh giá đã được ghi nhận.',
                ], 201);
            }
        );
    });

    Route::get(
        '/programs/{program}',
        [ProgramApiController::class, 'show']
    );
});