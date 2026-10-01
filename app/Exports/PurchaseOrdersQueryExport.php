<?php

namespace App\Exports;

use App\Models\PurchaseOrder;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PurchaseOrdersQueryExport implements FromQuery, ShouldAutoSize, WithChunkReading, WithHeadings, WithMapping
{
    public function __construct(private readonly array $filters) {}

    public function query(): Builder
    {
        return PurchaseOrder::query()
            ->with(['geOrder', 'supplier', 'creator'])
            ->when($this->filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $subQuery) use ($search): void {
                    $subQuery->where('po_number', 'like', "%{$search}%")
                        ->orWhereHas('geOrder', fn (Builder $orders) => $orders->where('order_number', 'like', "%{$search}%"))
                        ->orWhereHas('supplier', fn (Builder $suppliers) => $suppliers->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($this->filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->latest('order_date');
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function headings(): array
    {
        return ['PO Number', 'GE Order', 'Supplier', 'Order Date', 'Expected Delivery', 'Amount (PGK)', 'Status', 'Created By', 'Receiving Person Name', 'Receiving Person Position', 'Receiving Person Branch', 'Receiving Person Phone', 'Receiving Person Email'];
    }

    public function map($order): array
    {
        return [$order->po_number, $order->geOrder?->order_number, $order->supplier?->name, $order->order_date?->format('Y-m-d'), $order->expected_delivery_date?->format('Y-m-d'), (float) $order->total_amount, $order->status, $order->creator?->name, $order->receiving_person_name, $order->receiving_person_position, $order->receiving_person_branch, $order->receiving_person_phone, $order->receiving_person_email];
    }
}
