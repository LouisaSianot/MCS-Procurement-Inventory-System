<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class ReportsExport implements FromArray, ShouldAutoSize, WithTitle
{
    public function __construct(private readonly array $report, private readonly array $filters) {}

    public function title(): string
    {
        return 'Reports';
    }

    public function array(): array
    {
        $overview = $this->report['procurementOverview'];
        $inventory = $this->report['inventorySummary'];
        $rows = [
            ['MCS Procurement & Inventory Report'],
            ['Generated', now()->format('Y-m-d H:i')],
            ['Filters', $this->filterDescription()],
            [],
            ['Procurement Overview'],
            ['Total Purchase Orders', $overview->total_purchase_orders],
            ['Total Procurement Value (PGK)', (float) $overview->total_procurement_value],
            [],
            ['Purchase Order Status', 'Purchase Orders', 'Procurement Value (PGK)'],
        ];

        foreach ($this->report['statusBreakdown'] as $status) {
            $rows[] = [ucfirst($status->status), $status->purchase_order_count, (float) $status->procurement_value];
        }

        $rows[] = [];
        $rows[] = ['Procurement Activity', 'Purchase Orders', 'Procurement Value (PGK)'];
        foreach ($this->report['procurementActivity'] as $activity) {
            $rows[] = [$activity->month_start, $activity->purchase_order_count, (float) $activity->procurement_value];
        }

        $rows[] = [];
        $rows[] = ['Top Suppliers', 'Purchase Orders', 'Procurement Value (PGK)'];
        foreach ($this->report['supplierSummary'] as $supplier) {
            $rows[] = [$supplier->name, $supplier->purchase_order_count, (float) $supplier->procurement_value];
        }

        $rows[] = [];
        $rows[] = ['Most Purchased Items', 'UOM', 'Quantity', 'Procurement Value (PGK)'];
        foreach ($this->report['itemPurchasingSummary'] as $item) {
            $rows[] = [$item->item_name, $item->unit, (float) $item->quantity_ordered, (float) $item->procurement_value];
        }

        $rows[] = [];
        $rows[] = ['Inventory Summary'];
        $rows[] = ['Inventory Items', $inventory->inventory_item_count];
        $rows[] = ['Current Stock', (float) $inventory->current_stock];
        $rows[] = ['Inventory Value (PGK)', (float) $inventory->inventory_value];
        $rows[] = ['Low-stock Items', $inventory->low_stock_count];
        $rows[] = [];
        $rows[] = ['Low Stock Items', 'Branch', 'Current Stock', 'Reorder Level', 'Reorder Quantity', 'Inventory Value (PGK)'];
        foreach ($this->report['lowStockItems'] as $itemBranch) {
            $rows[] = [$itemBranch->item?->description, $itemBranch->branchRecord?->name, (float) $itemBranch->current_stock, (float) $itemBranch->reorder_level, (float) $itemBranch->reorder_quantity, (float) $itemBranch->inventoryValue()];
        }

        return $rows;
    }

    private function filterDescription(): string
    {
        return collect($this->filters)->filter()->map(fn ($value, $key) => str($key)->replace('_', ' ')->title().': '.$value)->implode('; ') ?: 'All records';
    }
}
