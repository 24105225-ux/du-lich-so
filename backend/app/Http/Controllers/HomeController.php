<?php

namespace App\Http\Controllers;

use App\Services\ProgramCatalogService;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(
        private ProgramCatalogService $programs
    ) {
    }

    public function index(): View
    {
        $programs = $this->programs->search([], 6);

        return view('home', [
            'programs' => $programs,
        ]);
    }
}
