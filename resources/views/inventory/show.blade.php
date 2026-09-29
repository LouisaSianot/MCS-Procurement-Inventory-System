<x-app-layout title="Inventory: {{ $itemBranch->item->description }}">
    <x-page-header title="{{ $itemBranch->item->description }}" description="Item #{{ $itemBranch->item_id }} · Total stock {{ number_format($totalStock, 2) }}" :breadcrumbs="[['label' => 'Inventory', 'url' => route('inventory.index')], ['label' => $itemBranch->item->description]]">
        <x-slot name="actions">
            @can('transfer', App\Models\ItemBranch::class)<a href="{{ route('inventory.transfers.create', ['item_id' => $itemBranch->item_id]) }}" class="btn btn-primary"><i data-lucide="arrow-left-right" class="h-4 w-4"></i> Move Stock</a>@endcan
            <a href="{{ route('inventory.index') }}" class="btn btn-secondary"><i data-lucide="arrow-left" class="h-4 w-4"></i> Back to Inventory</a>
        </x-slot>
    </x-page-header>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <section class="card xl:col-span-2">
            <div class="border-b border-slate-200 p-5">
                <h3 class="text-base font-semibold text-slate-900">Item information</h3>
            </div>
            <dl class="grid grid-cols-1 gap-x-6 gap-y-5 p-5 sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wider text-slate-400">Item ID</dt>
                    <dd class="mt-1 font-mono text-sm text-slate-800">{{ $itemBranch->item_id }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wider text-slate-400">UOM</dt>
                    <dd class="mt-1 text-sm text-slate-800">{{ $itemBranch->uom ?? $itemBranch->item->uom }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wider text-slate-400">Category</dt>
                    <dd class="mt-1 text-sm text-slate-800">{{ $itemBranch->item->category }} · {{ $itemBranch->item->sub_category }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wider text-slate-400">Model Number</dt>
                    <dd class="mt-1 text-sm text-slate-800">{{ $itemBranch->item->model_number ?: 'Not assigned' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wider text-slate-400">Primary Supplier</dt>
                    <dd class="mt-1 text-sm text-slate-800">{{ $itemBranch->item->supplier?->name ?? 'Not assigned' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wider text-slate-400">Branch</dt>
                    <dd class="mt-1 text-sm text-slate-800">{{ $itemBranch->branchRecord->name }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wider text-slate-400">Location</dt>
                    <dd class="mt-1 text-sm text-slate-800">{{ $itemBranch->locationRecord?->name ?? $itemBranch->location ?? 'Not set' }}</dd>
                </div>
            </dl>
        </section>
        <section class="card">
            <div class="border-b border-slate-200 p-5">
                <h3 class="text-base font-semibold text-slate-900">Stock summary</h3>
            </div>
            <div class="space-y-4 p-5">
                <div>
                    <p class="text-xs uppercase tracking-wider text-slate-400">Total Stock</p>
                    <p class="mt-1 text-3xl font-bold tracking-tight text-slate-900">{{ number_format($totalStock, 2) }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ $itemBranch->current_stock }} at {{ $itemBranch->locationRecord?->name ?? 'this location' }}</p>
                </div>
                <div class="grid grid-cols-2 gap-3 border-t border-slate-100 pt-4">
                    <div>
                        <p class="text-xs text-slate-400">Unit Cost</p>
                        <p class="mt-1 font-semibold">K {{ number_format((float) $itemBranch->unit_cost, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400">Inventory Value</p>
                        <p class="mt-1 font-semibold">K {{ number_format($itemBranch->inventoryValue(), 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400">Reorder Level</p>
                        <p class="mt-1 font-semibold">{{ $itemBranch->reorder_level }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400">Reorder Quantity</p>
                        <p class="mt-1 font-semibold">{{ $itemBranch->reorder_quantity }}</p>
                    </div>
                </div><x-status-badge :status="$itemBranch->stockStatusLabel()" />
            </div>
        </section>
    </div>

    <section class="card mt-6">
        <div class="border-b border-slate-200 p-5">
            <h3 class="text-base font-semibold text-slate-900">Location breakdown</h3>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Branch</th><th>Location</th><th class="text-right">Stock</th></tr></thead>
                <tbody>
                    @foreach($locationStocks as $stockRecord)
                    <tr>
                        <td>{{ $stockRecord->branchRecord->name }}</td>
                        <td>{{ $stockRecord->locationRecord?->name ?? $stockRecord->location ?? '—' }}</td>
                        <td class="text-right font-semibold tabular-nums">{{ $stockRecord->current_stock }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot><tr><th colspan="2" class="text-right">Total</th><th class="text-right tabular-nums">{{ number_format($totalStock, 2) }}</th></tr></tfoot>
            </table>
        </div>
    </section>

    @if($itemBranch->item->is_serialized)
    <section class="card mt-6">
        <div class="border-b border-slate-200 p-5">
            <h3 class="text-base font-semibold text-slate-900">Serialized units</h3>
            <p class="mt-1 text-sm text-slate-500">Individual units currently associated with this branch.</p>
        </div>
        @if($itemBranch->serials->isNotEmpty())
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Serial number</th>
                        <th>Status</th>
                        <th>Date added</th>
                    </tr>
                </thead>
                <tbody>@foreach($itemBranch->serials as $serial)<tr>
                        <td class="font-mono text-sm">{{ $serial->serial_number }}</td>
                        <td><x-status-badge :status="ucfirst($serial->status)" /></td>
                        <td>{{ $serial->created_at?->format('d M Y') }}</td>
                    </tr>@endforeach</tbody>
            </table>
        </div>
        @else
        <div class="p-5 text-sm text-slate-500">No serialized units have been received for this branch.</div>
        @endif
    </section>
    @endif

    <section class="card mt-6">
        <div class="border-b border-slate-200 p-5">
            <h3 class="text-base font-semibold text-slate-900">Movement history</h3>
            <p class="mt-1 text-sm text-slate-500">Receipts and transfers for this item, across all locations.</p>
        </div>
        @if($movements->isNotEmpty())<x-table-section title="Movement history" :count="$movements->count()" :open="true">
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Purchase order</th>
                            <th>Receipt</th>
                            <th>Performed by</th>
                            <th class="text-right">Quantity</th>
                            <th class="text-right">Unit Cost</th>
                            <th class="text-right">Stock After</th>
                            <th class="text-right">Destination Stock After</th>
                        </tr>
                    </thead>
                        <tbody>@foreach($movements as $movement)@php($receipt = $movement->purchaseReceiptItem?->receipt) <tr>
                            <td>{{ $movement->created_at->format('d M Y') }}</td>
                            <td>{{ ucwords(str_replace('_', ' ', $movement->type)) }}</td>
                            <td>{{ $movement->fromLocation?->name ?? $movement->itemBranch->locationRecord?->name ?? '—' }}</td>
                            <td>{{ $movement->toLocation?->name ?? '—' }}</td>
                            <td>{{ $receipt?->purchaseOrder?->po_number ?? '—' }}</td>
                            <td>{{ $receipt?->receipt_number ?? '—' }}</td>
                            <td>{{ $movement->performer?->name ?? '—' }}</td>
                            <td class="text-right font-medium {{ $movement->type === App\Models\InventoryMovement::TYPE_RECEIPT ? 'text-emerald-700' : 'text-slate-700' }}">{{ $movement->type === App\Models\InventoryMovement::TYPE_RECEIPT ? '+' : '' }}{{ $movement->quantity }}</td>
                            <td class="text-right">K {{ number_format((float) $movement->unit_cost, 2) }}</td>
                            <td class="text-right font-semibold">{{ $movement->stock_after }}</td>
                            <td class="text-right">{{ $movement->destination_stock_after ?? '—' }}</td>
                        </tr>@endforeach</tbody>
                </table>
            </div>
            <div class="border-t border-slate-200 px-5 py-4">{{ $movements->links() }}</div>
        </x-table-section>@else<x-empty-state icon="history" title="No inventory movements yet" message="Stock movements will appear here after purchase receipts are posted." />@endif
    </section>
</x-app-layout>
