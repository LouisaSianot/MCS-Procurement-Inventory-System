<?php

namespace App\Jobs;

use App\Exports\PurchaseOrdersQueryExport;
use App\Exports\PurchaseReceiptsQueryExport;
use App\Exports\ReportsExport;
use App\Models\ExportRequest;
use App\Services\ReportsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class GenerateExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;

    public int $tries = 2;

    public function __construct(public readonly int $exportRequestId)
    {
        $this->onQueue('exports');
    }

    public function handle(ReportsService $reports): void
    {
        $request = ExportRequest::findOrFail($this->exportRequestId);
        $request->update(['status' => ExportRequest::STATUS_PROCESSING]);

        $extension = 'xlsx';
        $filename = $request->filename ?: "export-{$request->id}.{$extension}";
        $path = "exports/{$filename}";

        $export = match ($request->type) {
            'purchase_orders' => new PurchaseOrdersQueryExport($request->filters ?? []),
            'purchase_receipts' => new PurchaseReceiptsQueryExport($request->filters ?? []),
            'reports' => new ReportsExport($reports->build($request->filters ?? []), $request->filters ?? []),
            default => throw new \InvalidArgumentException("Unsupported export type [{$request->type}]."),
        };

        Excel::store($export, $path, 'local');
        $request->update(['status' => ExportRequest::STATUS_COMPLETED, 'path' => $path, 'filename' => $filename]);
    }

    public function failed(Throwable $exception): void
    {
        ExportRequest::whereKey($this->exportRequestId)->update([
            'status' => ExportRequest::STATUS_FAILED,
            'error_message' => str($exception->getMessage())->limit(1000),
        ]);
    }
}
