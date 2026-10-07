<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Services\ProgramCatalogService;
use App\Services\PythonDataService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProgramController extends Controller
{
    public function __construct(
        private ProgramCatalogService $catalog,
        private PythonDataService $pythonData
    ) {
    }

    public function index(Request $request): View
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
        ]);

        $programs = $this->catalog->search(
            $filters,
            9
        );

        return view('programs.index', [
            'programs' => $programs,
            'filters' => $filters,
        ]);
    }

    public function show(Program $program): View
    {
        $program = $this->catalog->detail(
            $program
        );

        $recommendations = $this->pythonData->recommend($program->id, 6);

        return view('programs.show', [
            'program' => $program,
            'recommendations' => $recommendations,
        ]);
    }
}
