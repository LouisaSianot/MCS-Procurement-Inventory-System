<?php

namespace App\Http\Controllers;

use App\Exports\ReportsExport;
use App\Http\Requests\ReportFilterRequest;
use App\Jobs\GenerateExport;
use App\Models\Branch;
use App\Models\ExportRequest;
use App\Models\Supplier;
use App\Services\ReportsService;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Cache;

class ReportsController extends Controller
{
    public function index(ReportFilterRequest $request, ReportsService $reports): \Illuminate\View\View
    {
        $filters = $request->validated();

        return view('reports.index', [
            'title' => 'Reports',
            'filters' => $filters,
            'branches' => Cache::remember('lookup.branches.v3', now()->addMinutes(10), fn () => Branch::orderBy('name')->get(['id', 'name'])->toArray()),
            'suppliers' => Cache::remember('lookup.suppliers.v3', now()->addMinutes(10), fn () => Supplier::orderBy('name')->get(['id', 'name'])->toArray()),
            ...$reports->build($filters),
        ]);
    }

    public function export(ReportFilterRequest $request, ReportsService $reports, string $format)
    {
        $filters = $request->validated();

        if ($format === 'xlsx') {
            $export = ExportRequest::create([
                'user_id' => $request->user()->id,
                'type' => 'reports',
                'filters' => $filters,
                'status' => ExportRequest::STATUS_PENDING,
                'filename' => 'reports-' . now()->format('Y-m-d-His') . '.xlsx',
            ]);

            GenerateExport::dispatch($export->id);

            return back()->with('success', 'Your Excel export has been queued. It will be available shortly.');
        }

        $report = $reports->build($filters);
        $filename = 'reports-'.now()->format('Y-m-d');

        return Pdf::loadView('reports.exports.pdf', ['filters' => $filters, ...$report])
            ->setPaper('a4', 'landscape')
            ->download("{$filename}.pdf");
    }
}
