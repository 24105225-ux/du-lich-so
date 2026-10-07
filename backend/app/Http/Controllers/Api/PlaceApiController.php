<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Place;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlaceApiController extends Controller
{
    public function index(): JsonResponse
    {
        $places = Place::query()
            ->orderBy('id')
            ->paginate(20);

        return response()->json($places);
    }

    public function show(Place $place): JsonResponse
    {
        return response()->json([
            'data' => $place,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'province' => ['required', 'string', 'max:80'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'best_season' => ['nullable', 'string', 'max:80'],
            'visit_minutes' => ['nullable', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'source_note' => ['nullable', 'string', 'max:255'],
        ]);

        $place = Place::create($validated);

        return response()->json([
            'message' => 'Tạo địa điểm thành công.',
            'data' => $place,
        ], 201);
    }

    public function update(
        Request $request,
        Place $place
    ): JsonResponse {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'province' => ['required', 'string', 'max:80'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'best_season' => ['nullable', 'string', 'max:80'],
            'visit_minutes' => ['nullable', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'source_note' => ['nullable', 'string', 'max:255'],
        ]);

        $place->update($validated);
        $place->refresh();

        return response()->json([
            'message' => 'Cập nhật địa điểm thành công.',
            'data' => $place,
        ]);
    }

    public function destroy(Place $place): JsonResponse
    {
        try {
            $place->delete();
        } catch (QueryException $e) {
            if ((string) $e->getCode() === '23000') {
                return response()->json([
                    'message' =>
                        'Không thể xóa địa điểm đang được sử dụng.',
                ], 409);
            }

            throw $e;
        }

        return response()->json([
            'message' => 'Xóa địa điểm thành công.',
        ]);
    }
}