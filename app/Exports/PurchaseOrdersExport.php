<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PurchaseOrdersExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(private readonly Collection $orders) {}
    public function collection(): Collection { return $this->orders; }
    public function headings(): array { return ['PO Number', 'GE Order', 'Supplier', 'Order Date', 'Expected Delivery', 'Amount (PGK)', 'Status', 'Created By']; }
    public function map($order): array { return [$order->po_number, $order->geOrder?->order_number, $order->supplier?->name, $order->order_date?->format('Y-m-d'), $order->expected_delivery_date?->format('Y-m-d'), (float) $order->total_amount, $order->status, $order->creator?->name]; }
}
