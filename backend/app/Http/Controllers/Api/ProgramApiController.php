<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProgramResource;
use App\Models\Program;
use App\Services\ProgramCatalogService;
use Illuminate\Http\Request;

class ProgramApiController extends Controller
{
    public function __construct(
        private ProgramCatalogService $catalog
    ) {
    }

    public function index(Request $request)
    {
        $filters = $request->validate([
            'keyword' => [
                'nullable',
                'string',
                'max:120',
            ],

            'education_level' => [
                'nullable',
                'in:Tieu hoc,THCS,THPT',
            ],

            'min_price' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'max_price' => [
                'nullable',
                'numeric',
                'min:0',
                'gte:min_price',
            ],

            'sort' => [
                'nullable',
                'in:price_asc,price_desc,newest',
            ],

            'per_page' => [
                'nullable',
                'integer',
                'min:1',
                'max:50',
            ],
        ]);

        $programs = $this->catalog->search(
            $filters,
            (int) (
                $filters['per_page'] ?? 9
            )
        );

        return ProgramResource::collection(
            $programs
        );
    }

    public function show(Program $program)
    {
        return new ProgramResource(
            $this->catalog->detail($program)
        );
    }
}
