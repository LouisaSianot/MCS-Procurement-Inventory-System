<?php

namespace App\Exports;

use App\Models\PurchaseReceipt;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PurchaseReceiptsQueryExport implements FromQuery, ShouldAutoSize, WithChunkReading, WithHeadings, WithMapping
{
    public function __construct(private readonly array $filters = []) {}

    public function query(): Builder
    {
        return PurchaseReceipt::query()
            ->with(['purchaseOrder.supplier', 'receiver'])
            ->latest('received_at')
            ->latest('id');
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function headings(): array
    {
        return ['Receipt Number', 'Purchase Order', 'Supplier', 'Received Date', 'Supplier Reference', 'Received By', 'Notes'];
    }

    public function map($receipt): array
    {
        return [$receipt->receipt_number, $receipt->purchaseOrder?->po_number, $receipt->purchaseOrder?->supplier?->name, $receipt->received_at?->format('Y-m-d'), $receipt->supplier_delivery_reference, $receipt->receiver?->name, $receipt->notes];
    }
}
