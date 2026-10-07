<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AuditLogService;
use App\Services\ProgramReviewService;
use Illuminate\Http\Request;
use RuntimeException;

class ProgramReviewController extends Controller
{
    public function __construct(
        private ProgramReviewService $reviews,
        private AuditLogService $auditLogs,
    ) {}

    public function eligibility(Request $request, int $program)
    {
        return response()->json(
            $this->reviews->eligibility($request->user(), $program)
        );
    }

    public function store(Request $request, int $program)
    {
        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $review = $this->reviews->create(
                $request->user(),
                $program,
                (int) $data['rating'],
                $data['comment'] ?? null,
            );
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }

        $this->auditLogs->record(
            $request,
            'CREATE',
            'program_review',
            $review->id,
            null,
            ['program_id' => $program, 'rating' => $review->rating],
        );

        return response()->json([
            'data' => ['id' => $review->id],
            'message' => 'Đánh giá đã được ghi nhận.',
        ], 201);
    }
}
