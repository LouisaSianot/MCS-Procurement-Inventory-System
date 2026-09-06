<?php

namespace App\Services;

use App\Models\ItemBranch;
use App\Models\PurchaseOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ReportsService
{
    /**
     * Build all read-only report sections from the current transactional data.
     */
    public function build(array $filters): array
    {
        $purchaseOrders = $this->filteredPurchaseOrders($filters);
        $inventory = $this->filteredInventory($filters);
        $monthExpression = $this->monthExpression();

        return [
            'procurementOverview' => (clone $purchaseOrders)
                ->selectRaw('COUNT(*) AS total_purchase_orders')
                ->selectRaw('COALESCE(SUM(total_amount), 0) AS total_procurement_value')
                ->first(),

            'statusBreakdown' => (clone $purchaseOrders)
                ->select('status')
                ->selectRaw('COUNT(*) AS purchase_order_count')
                ->selectRaw('COALESCE(SUM(total_amount), 0) AS procurement_value')
                ->groupBy('status')
                ->orderBy('status')
                ->get(),

            'procurementActivity' => (clone $purchaseOrders)
                ->whereNotNull('purchase_orders.order_date')
                ->selectRaw("{$monthExpression} AS month_start")
                ->selectRaw('COUNT(*) AS purchase_order_count')
                ->selectRaw('COALESCE(SUM(total_amount), 0) AS procurement_value')
                ->groupByRaw($monthExpression)
                ->orderByRaw($monthExpression)
                ->get(),

            'supplierSummary' => (clone $purchaseOrders)
                ->join('suppliers', 'suppliers.id', '=', 'purchase_orders.supplier_id')
                ->select('suppliers.id', 'suppliers.name')
                ->selectRaw('COUNT(purchase_orders.id) AS purchase_order_count')
                ->selectRaw('COALESCE(SUM(purchase_orders.total_amount), 0) AS procurement_value')
                ->groupBy('suppliers.id', 'suppliers.name')
                ->orderByDesc('procurement_value')
                ->orderBy('suppliers.name')
                ->limit(10)
                ->get(),

            'itemPurchasingSummary' => $this->itemPurchasingSummary($filters),

            'inventorySummary' => (clone $inventory)
                ->selectRaw('COUNT(*) AS inventory_item_count')
                ->selectRaw('COALESCE(SUM(current_stock), 0) AS current_stock')
                ->selectRaw('COALESCE(SUM(current_stock * unit_cost), 0) AS inventory_value')
                ->selectRaw('COALESCE(SUM(CASE WHEN current_stock <= reorder_level THEN 1 ELSE 0 END), 0) AS low_stock_count')
                ->first(),

            'lowStockItems' => (clone $inventory)
                ->with(['item', 'branchRecord'])
                ->whereColumn('current_stock', '<=', 'reorder_level')
                ->orderBy('current_stock')
                ->orderBy('item_id')
                ->limit(10)
                ->get(),
        ];
    }

    private function filteredPurchaseOrders(array $filters): Builder
    {
        return PurchaseOrder::query()
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $dateFrom) => $query->whereDate('purchase_orders.order_date', '>=', $dateFrom))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $dateTo) => $query->whereDate('purchase_orders.order_date', '<=', $dateTo))
            ->when($filters['branch_id'] ?? null, fn (Builder $query, int $branchId) => $query->where('purchase_orders.branch_id', $branchId))
            ->when($filters['supplier_id'] ?? null, fn (Builder $query, int $supplierId) => $query->where('purchase_orders.supplier_id', $supplierId));
    }

    private function filteredInventory(array $filters): Builder
    {
        return ItemBranch::query()
            ->whereNotNull('branch_id')
            ->whereHas('item')
            ->whereHas('branchRecord')
            ->when($filters['branch_id'] ?? null, fn (Builder $query, int $branchId) => $query->where('branch_id', $branchId));
    }

    private function itemPurchasingSummary(array $filters)
    {
        return $this->filteredPurchaseOrders($filters)
            ->join('purchase_order_items', 'purchase_order_items.purchase_order_id', '=', 'purchase_orders.id')
            ->leftJoin('items', 'items.id', '=', 'purchase_order_items.item_id')
            ->selectRaw('COALESCE(items.description, purchase_order_items.description) AS item_name')
            ->selectRaw('MAX(purchase_order_items.unit) AS unit')
            ->selectRaw('COALESCE(SUM(purchase_order_items.quantity), 0) AS quantity_ordered')
            ->selectRaw('COALESCE(SUM(purchase_order_items.total), 0) AS procurement_value')
            ->groupByRaw('COALESCE(items.description, purchase_order_items.description)')
            ->orderByDesc('procurement_value')
            ->orderBy('item_name')
            ->limit(10)
            ->get();
    }

    private function monthExpression(): string
    {
        return DB::connection()->getDriverName() === 'pgsql'
            ? "DATE_TRUNC('month', order_date)::date"
            : "strftime('%Y-%m-01', order_date)";
    }
}
