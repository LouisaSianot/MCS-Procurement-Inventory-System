<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReportFilterRequest;
use App\Models\Branch;
use App\Models\Supplier;
use App\Services\ReportsService;

class ReportsController extends Controller
{
    public function index(ReportFilterRequest $request, ReportsService $reports): \Illuminate\View\View
    {
        $filters = $request->validated();

        return view('reports.index', [
            'title' => 'Reports',
            'filters' => $filters,
            'branches' => Branch::orderBy('name')->get(['id', 'name']),
            'suppliers' => Supplier::orderBy('name')->get(['id', 'name']),
            ...$reports->build($filters),
        ]);
    }
}
