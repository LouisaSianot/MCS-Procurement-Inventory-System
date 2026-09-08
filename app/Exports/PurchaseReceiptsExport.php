<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PurchaseReceiptsExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(private readonly Collection $receipts) {}
    public function collection(): Collection { return $this->receipts; }
    public function headings(): array { return ['Receipt Number', 'Purchase Order', 'Supplier', 'Received Date', 'Supplier Reference', 'Received By', 'Notes']; }
    public function map($receipt): array { return [$receipt->receipt_number, $receipt->purchaseOrder?->po_number, $receipt->purchaseOrder?->supplier?->name, $receipt->received_at?->format('Y-m-d'), $receipt->supplier_delivery_reference, $receipt->receiver?->name, $receipt->notes]; }
}
