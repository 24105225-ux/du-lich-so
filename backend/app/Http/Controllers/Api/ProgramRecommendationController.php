<?php

// D:\CODE\du-lich-so\backend\app\Http\Controllers\Api\ProgramRecommendationController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Services\PythonDataService;

class ProgramRecommendationController extends Controller
{
    public function show(
        Program $program,
        PythonDataService $pythonDataService
    ) {
        $result = $pythonDataService->recommend(
            $program->id,
            6
        );

        return response()->json([
            'data' => [
                'program_id' => $program->id,
                'source' => $result['source'],
                'items' => $result['items'],
            ],
        ]);
    }
}
