<?php

namespace App\Http\Controllers;

use App\Models\GEOrder;
use App\Models\ItemBranch;
use App\Models\PurchaseOrder;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $recentOrders = GEOrder::query()
            ->with('supplier:id,name')
            ->latest('order_date')
            ->latest('id')
            ->limit(5)
            ->get(['id', 'order_number', 'supplier_id', 'inventory_flag', 'order_date', 'total_amount', 'status'])
            ->map(fn (GEOrder $order) => (object) [
                'id' => $order->id,
                'number' => $order->order_number,
                'supplier' => $order->supplier?->name ?? 'N/A',
                'type' => strtoupper($order->inventory_flag ?? 'NON-STOCK'),
                'date' => $order->order_date?->format('d M Y') ?? '-',
                'amount' => 'K ' . number_format((float) ($order->total_amount ?? 0), 2),
                'status' => $order->status,
            ]);

        $orderCounts = GEOrder::query()
            ->select('status')
            ->selectRaw('COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $inventorySummary = ItemBranch::query()
            ->selectRaw('COUNT(*) as total_inventory_items')
            ->selectRaw('SUM(CASE WHEN current_stock > reorder_level THEN 1 ELSE 0 END) as stock_normal')
            ->selectRaw('SUM(CASE WHEN current_stock > 0 AND current_stock <= reorder_level THEN 1 ELSE 0 END) as stock_low')
            ->selectRaw('SUM(CASE WHEN current_stock = 0 THEN 1 ELSE 0 END) as stock_out')
            ->first();

        $lowStockTable = ItemBranch::query()
            ->with(['item:id,description', 'branchRecord:id,name'])
            ->whereColumn('current_stock', '<=', 'reorder_level')
            ->orderByRaw('CASE WHEN current_stock = 0 THEN 0 ELSE 1 END')
            ->orderByRaw('(reorder_level - current_stock) DESC')
            ->limit(5)
            ->get()
            ->map(fn (ItemBranch $record) => (object) [
                'id' => $record->id,
                'name' => $record->item?->description ?? 'Item #' . $record->item_id,
                'current_stock' => $record->current_stock,
                'reorder_level' => $record->reorder_level,
                'shortfall' => max(0, (float) $record->reorder_level - (float) $record->current_stock),
                'status' => $record->stockStatus(),
                'location' => $record->location ?: ($record->branchRecord?->name ?? '—'),
            ]);

        $stockNormal = (int) ($inventorySummary->stock_normal ?? 0);
        $stockLow = (int) ($inventorySummary->stock_low ?? 0);
        $stockOut = (int) ($inventorySummary->stock_out ?? 0);

        return view('dashboard.index', [
            'recentOrders' => $recentOrders,
            'totalOrders' => (int) $orderCounts->sum(),
            'pendingOrders' => (int) $orderCounts->get(GEOrder::STATUS_PENDING, 0),
            'approvedOrders' => (int) $orderCounts->get(GEOrder::STATUS_APPROVED, 0),
            'awaitingReceipt' => PurchaseOrder::query()
                ->whereIn('status', [PurchaseOrder::STATUS_ORDERED, PurchaseOrder::STATUS_BACKORDER, PurchaseOrder::STATUS_PARTIALLY_RECEIVED])
                ->count(),
            'lowStockItems' => $stockLow + $stockOut,
            'totalInventoryItems' => (int) ($inventorySummary->total_inventory_items ?? 0),
            'stockNormal' => $stockNormal,
            'stockLow' => $stockLow,
            'stockOut' => $stockOut,
            'lowStockTable' => $lowStockTable,
        ]);
    }
}
