<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Services\ProgramCatalogService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProgramController extends Controller
{
    public function __construct(
        private ProgramCatalogService $catalog
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
                'in:TH,THCS,THPT',
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

        return view('programs.show', [
            'program' => $program,
        ]);
    }
}
