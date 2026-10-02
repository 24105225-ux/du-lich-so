<?php

// D:\CODE\du-lich-so\backend\app\Services\PythonDataService.php

namespace App\Services;

use App\Models\Program;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PythonDataService
{
    public function __construct()
    {
    }

    /**
     * Gọi Python để lấy chương trình tương tự.
     *
     * Bảo vệ:
     * - cache 30 phút
     * - timeout 3 giây
     * - retry 2 lần
     * - fallback khi Python lỗi
     */
    public function recommend(
        int $programId,
        int $k = 6
    ): array {
        $cacheKey = "dt17-recommend:{$programId}:{$k}";

        return Cache::remember(
            $cacheKey,
            now()->addMinutes(30),
            function () use (
                $programId,
                $k
            ) {
                $baseUrl = rtrim(
                    (string) config(
                        'services.python.url'
                    ),
                    '/'
                );

                $token = (string) config(
                    'services.python.token'
                );

                try {
                    $response = Http::withHeaders([
                        'X-Service-Token' => $token,
                        'Accept' => 'application/json',
                    ])
                        ->timeout(3)
                        ->retry(
                            2,
                            200
                        )
                        ->get(
                            "{$baseUrl}/recommend/{$programId}",
                            [
                                'k' => $k,
                            ]
                        );

                    if (
                        $response->successful()
                    ) {
                        return [
                            'source' => 'python',
                            'items' => $response->json(
                                'items',
                                []
                            ),
                        ];
                    }

                    Log::warning(
                        'Python DT-17 tra ve loi',
                        [
                            'status' =>
                                $response->status(),
                            'program_id' =>
                                $programId,
                        ]
                    );
                } catch (\Throwable $exception) {
                    Log::error(
                        'Khong goi duoc Python DT-17',
                        [
                            'program_id' =>
                                $programId,
                            'message' =>
                                $exception->getMessage(),
                        ]
                    );
                }

                return [
                    'source' => 'fallback',
                    'items' => $this->fallback(
                        $programId,
                        $k
                    ),
                ];
            }
        );
    }

    /**
     * Phương án dự phòng nếu Python không hoạt động.
     *
     * Dùng quy tắc nghiệp vụ đơn giản:
     * - cùng cấp học
     * - giá nằm trong khoảng ±60%
     * - loại bỏ chương trình hiện tại
     */
    private function fallback(
        int $programId,
        int $k
    ): array {
        $program = Program::find(
            $programId
        );

        if (! $program) {
            return [];
        }

        $minPrice =
            (float) $program->base_cost_per_student
            * 0.4;

        $maxPrice =
            (float) $program->base_cost_per_student
            * 1.6;

        return Program::query()
            ->where(
                'id',
                '!=',
                $programId
            )
            ->where(
                'education_level',
                $program->education_level
            )
            ->whereBetween(
                'base_cost_per_student',
                [
                    $minPrice,
                    $maxPrice,
                ]
            )
            ->orderBy(
                'base_cost_per_student'
            )
            ->limit($k)
            ->get([
                'id as program_id',
                'name as title',
                'education_level',
                'base_cost_per_student',
            ])
            ->toArray();
    }

    /**
     * Xóa cache gợi ý.
     */
    public function clearRecommendationCache(
        int $programId
    ): void {
        foreach (
            [3, 4, 5, 6, 7, 8]
            as $k
        ) {
            Cache::forget(
                "dt17-recommend:{$programId}:{$k}"
            );
        }
    }
}
