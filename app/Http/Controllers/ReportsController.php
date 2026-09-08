<?php

namespace App\Http\Controllers;

use App\Exports\ReportsExport;
use App\Http\Requests\ReportFilterRequest;
use App\Models\Branch;
use App\Models\Supplier;
use App\Services\ReportsService;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

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

    public function export(ReportFilterRequest $request, ReportsService $reports, string $format)
    {
        $filters = $request->validated();
        $report = $reports->build($filters);
        $filename = 'reports-'.now()->format('Y-m-d');

        if ($format === 'xlsx') {
            return Excel::download(new ReportsExport($report, $filters), "{$filename}.xlsx");
        }

        return Pdf::loadView('reports.exports.pdf', ['filters' => $filters, ...$report])
            ->setPaper('a4', 'landscape')
            ->download("{$filename}.pdf");
    }
}
