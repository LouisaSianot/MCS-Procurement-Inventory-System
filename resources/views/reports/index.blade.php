<x-app-layout title="Reports">
    <x-page-header title="Reports" description="Read-only procurement and inventory reporting from current transactional data." />

    <div class="card mb-6 overflow-hidden">
        <form method="GET" action="{{ route('reports.index') }}" class="border-b border-slate-200 p-5">
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div>
                    <x-input-label for="date_from" value="From date" />
                    <x-text-input id="date_from" name="date_from" type="date" class="mt-1 block w-full" :value="$filters['date_from'] ?? ''" />
                    <x-input-error :messages="$errors->get('date_from')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="date_to" value="To date" />
                    <x-text-input id="date_to" name="date_to" type="date" class="mt-1 block w-full" :value="$filters['date_to'] ?? ''" />
                    <x-input-error :messages="$errors->get('date_to')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="branch_id" value="Branch" />
                    <select id="branch_id" name="branch_id" class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                        <option value="">All branches</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" @selected((string) ($filters['branch_id'] ?? '') === (string) $branch->id)>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('branch_id')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="supplier_id" value="Supplier" />
                    <select id="supplier_id" name="supplier_id" class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                        <option value="">All suppliers</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" @selected((string) ($filters['supplier_id'] ?? '') === (string) $supplier->id)>{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('supplier_id')" class="mt-1" />
                </div>
            </div>
            <div class="mt-4 flex justify-end gap-2">
                <a href="{{ route('reports.index') }}" class="btn btn-ghost py-2">Clear</a>
                <button type="submit" class="btn btn-primary py-2"><i data-lucide="filter" class="h-4 w-4"></i> Apply filters</button>
            </div>
        </form>
    </div>

    <section aria-labelledby="procurement-overview-heading">
        <div class="mb-4 flex items-center justify-between"><h2 id="procurement-overview-heading" class="text-lg font-bold text-slate-900">Procurement overview</h2><span class="text-sm text-slate-500">Based on purchase orders</span></div>
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-stat-card title="Total Purchase Orders" :value="$procurementOverview->total_purchase_orders" icon="shopping-cart" icon-color="brand" />
            <x-stat-card title="Total Procurement Value" :value="'K ' . number_format((float) $procurementOverview->total_procurement_value, 2)" icon="banknote" icon-color="emerald" />
            <x-stat-card title="Ordered Purchase Orders" :value="$statusBreakdown->firstWhere('status', 'ordered')->purchase_order_count ?? 0" icon="truck" icon-color="sky" />
            <x-stat-card title="Fully Received" :value="$statusBreakdown->firstWhere('status', 'fully received')->purchase_order_count ?? 0" icon="package-check" icon-color="violet" />
        </div>
    </section>

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        <section class="card overflow-hidden" aria-labelledby="status-heading">
            <div class="border-b border-slate-200 px-5 py-4"><h2 id="status-heading" class="font-semibold text-slate-900">Purchase order status</h2></div>
            @if ($statusBreakdown->isNotEmpty())
                <div class="table-wrap"><table class="data-table"><thead><tr><th>Status</th><th class="text-right">Purchase orders</th><th class="text-right">Procurement value</th></tr></thead><tbody>
                    @foreach ($statusBreakdown as $status)
                        <tr><td><x-status-badge :status="$status->status" /></td><td class="text-right tabular-nums">{{ $status->purchase_order_count }}</td><td class="text-right font-medium tabular-nums">K {{ number_format((float) $status->procurement_value, 2) }}</td></tr>
                    @endforeach
                </tbody></table></div>
            @else
                <x-empty-state icon="shopping-cart" title="No purchase orders found" message="No purchase orders match the selected filters." />
            @endif
        </section>

        <section class="card overflow-hidden" aria-labelledby="activity-heading">
            <div class="border-b border-slate-200 px-5 py-4"><h2 id="activity-heading" class="font-semibold text-slate-900">Procurement activity</h2></div>
            @if ($procurementActivity->isNotEmpty())
                <div class="table-wrap"><table class="data-table"><thead><tr><th>Month</th><th class="text-right">Purchase orders</th><th class="text-right">Procurement value</th></tr></thead><tbody>
                    @foreach ($procurementActivity as $activity)
                        <tr><td>{{ \Carbon\Carbon::parse($activity->month_start)->format('M Y') }}</td><td class="text-right tabular-nums">{{ $activity->purchase_order_count }}</td><td class="text-right font-medium tabular-nums">K {{ number_format((float) $activity->procurement_value, 2) }}</td></tr>
                    @endforeach
                </tbody></table></div>
            @else
                <x-empty-state icon="calendar-days" title="No procurement activity" message="No dated purchase orders match the selected filters." />
            @endif
        </section>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        <section class="card overflow-hidden" aria-labelledby="supplier-heading">
            <div class="border-b border-slate-200 px-5 py-4"><h2 id="supplier-heading" class="font-semibold text-slate-900">Top suppliers</h2></div>
            @if ($supplierSummary->isNotEmpty())
                <div class="table-wrap"><table class="data-table"><thead><tr><th>Supplier</th><th class="text-right">Purchase orders</th><th class="text-right">Procurement value</th></tr></thead><tbody>
                    @foreach ($supplierSummary as $supplier)
                        <tr><td class="font-medium">{{ $supplier->name }}</td><td class="text-right tabular-nums">{{ $supplier->purchase_order_count }}</td><td class="text-right font-medium tabular-nums">K {{ number_format((float) $supplier->procurement_value, 2) }}</td></tr>
                    @endforeach
                </tbody></table></div>
            @else
                <x-empty-state icon="truck" title="No supplier data" message="No supplier activity matches the selected filters." />
            @endif
        </section>

        <section class="card overflow-hidden" aria-labelledby="items-heading">
            <div class="border-b border-slate-200 px-5 py-4"><h2 id="items-heading" class="font-semibold text-slate-900">Most purchased items</h2></div>
            @if ($itemPurchasingSummary->isNotEmpty())
                <div class="table-wrap"><table class="data-table"><thead><tr><th>Item</th><th>UOM</th><th class="text-right">Quantity</th><th class="text-right">Procurement value</th></tr></thead><tbody>
                    @foreach ($itemPurchasingSummary as $item)
                        <tr><td class="font-medium">{{ $item->item_name }}</td><td>{{ $item->unit ?: '—' }}</td><td class="text-right tabular-nums">{{ number_format((float) $item->quantity_ordered, 2) }}</td><td class="text-right font-medium tabular-nums">K {{ number_format((float) $item->procurement_value, 2) }}</td></tr>
                    @endforeach
                </tbody></table></div>
            @else
                <x-empty-state icon="package" title="No item purchasing data" message="No purchase order lines match the selected filters." />
            @endif
        </section>
    </div>

    <section class="mt-6" aria-labelledby="inventory-heading">
        <div class="mb-4 flex items-center justify-between"><h2 id="inventory-heading" class="text-lg font-bold text-slate-900">Inventory summary</h2><span class="text-sm text-slate-500">Current inventory state{{ isset($filters['branch_id']) ? ' for the selected branch' : '' }}</span></div>
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-stat-card title="Inventory Items" :value="$inventorySummary->inventory_item_count" icon="boxes" icon-color="brand" />
            <x-stat-card title="Current Stock" :value="number_format((float) $inventorySummary->current_stock, 2)" icon="package" icon-color="sky" />
            <x-stat-card title="Inventory Value" :value="'K ' . number_format((float) $inventorySummary->inventory_value, 2)" icon="wallet-cards" icon-color="emerald" />
            <x-stat-card title="Low-stock Items" :value="$inventorySummary->low_stock_count" icon="alert-triangle" icon-color="rose" />
        </div>
    </section>

    <section class="card mt-6 overflow-hidden" aria-labelledby="low-stock-heading">
        <div class="border-b border-slate-200 px-5 py-4"><h2 id="low-stock-heading" class="font-semibold text-slate-900">Low stock</h2><p class="mt-1 text-sm text-slate-500">Current stock at or below the reorder level.</p></div>
        @if ($lowStockItems->isNotEmpty())
            <div class="table-wrap"><table class="data-table"><thead><tr><th>Item</th><th>Branch</th><th class="text-right">Current stock</th><th class="text-right">Reorder level</th><th class="text-right">Reorder quantity</th><th class="text-right">Inventory value</th></tr></thead><tbody>
                @foreach ($lowStockItems as $itemBranch)
                    <tr><td class="font-medium">{{ $itemBranch->item->description }}</td><td>{{ $itemBranch->branchRecord->name }}</td><td class="text-right tabular-nums">{{ number_format((float) $itemBranch->current_stock, 2) }}</td><td class="text-right tabular-nums">{{ number_format((float) $itemBranch->reorder_level, 2) }}</td><td class="text-right tabular-nums">{{ number_format((float) $itemBranch->reorder_quantity, 2) }}</td><td class="text-right font-medium tabular-nums">K {{ number_format($itemBranch->inventoryValue(), 2) }}</td></tr>
                @endforeach
            </tbody></table></div>
        @else
            <x-empty-state icon="check-circle-2" title="No low-stock items" message="No inventory records are currently at or below their reorder level." />
        @endif
    </section>
</x-app-layout>
