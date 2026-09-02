<x-app-layout title="Inventory">
    @php
        $search = $filters['search'] ?? request('search');
        $selectedBranch = (string) ($filters['branch'] ?? request('branch', ''));
        $selectedCategory = $filters['category'] ?? request('category');
        $selectedStatus = $filters['status'] ?? request('status');
        $statusLabels = ['in_stock' => 'In Stock', 'low_stock' => 'Low Stock', 'out_of_stock' => 'Out of Stock'];
    @endphp

    <x-page-header title="Inventory" description="Current stock by item and branch. Stock is updated through Purchase Receipts.">
        <x-slot name="actions"><a href="{{ route('receiving.create') }}" class="btn btn-secondary"><i data-lucide="package-check" class="h-4 w-4"></i> Receive Purchase</a></x-slot>
    </x-page-header>

    <section class="card animate-fade-in">
        <form method="GET" action="{{ route('inventory.index') }}" class="border-b border-slate-200 p-5">
            <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-5">
                <div class="relative xl:col-span-2"><i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"></i><input class="input pl-9" type="search" name="search" value="{{ $search }}" placeholder="Search item, item ID, or location"></div>
                <select name="branch" class="input"><option value="">All branches</option>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected($selectedBranch === (string) $branch->id)>{{ $branch->name }}</option>@endforeach</select>
                <select name="category" class="input"><option value="">All categories</option>@foreach($categories as $category)<option value="{{ $category }}" @selected($selectedCategory === $category)>{{ strtoupper($category) }}</option>@endforeach</select>
                <select name="status" class="input"><option value="">All stock statuses</option>@foreach($statusLabels as $value => $label)<option value="{{ $value }}" @selected($selectedStatus === $value)>{{ $label }}</option>@endforeach</select>
            </div>
            <div class="mt-3 flex justify-end gap-2"><a href="{{ route('inventory.index') }}" class="btn btn-ghost py-2">Clear</a><button class="btn btn-primary py-2"><i data-lucide="filter" class="h-4 w-4"></i> Apply filters</button></div>
        </form>

        @if ($inventory->isNotEmpty())
        <div class="table-wrap"><table class="data-table"><thead><tr><th>Item</th><th>UOM</th><th>Category</th><th>Branch</th><th>Location</th><th class="text-right">Current Stock</th><th class="text-right">Unit Cost</th><th class="text-right">Inventory Value</th><th>Reorder</th><th>Status</th><th class="text-right">Actions</th></tr></thead><tbody>
            @foreach ($inventory as $itemBranch)
            <tr>
                <td><a href="{{ route('inventory.show', $itemBranch) }}" class="font-medium text-brand-600 hover:text-brand-700">{{ $itemBranch->item->description }}</a><p class="mt-0.5 font-mono text-xs text-slate-400">Item #{{ $itemBranch->item_id }} · {{ $itemBranch->item->sub_category }}</p></td>
                <td>{{ $itemBranch->uom ?? $itemBranch->item->uom }}</td><td>{{ strtoupper($itemBranch->item->category) }}</td><td>{{ $itemBranch->branchRecord->name }}</td><td>{{ $itemBranch->location ?: '—' }}</td>
                <td class="text-right font-semibold tabular-nums">{{ $itemBranch->current_stock }}</td><td class="text-right tabular-nums">K {{ number_format((float) $itemBranch->unit_cost, 2) }}</td><td class="text-right font-medium tabular-nums">K {{ number_format($itemBranch->inventoryValue(), 2) }}</td>
                <td><span class="tabular-nums">{{ $itemBranch->reorder_level }}</span><p class="text-xs text-slate-400">Qty {{ $itemBranch->reorder_quantity }}</p></td><td><x-status-badge :status="$itemBranch->stockStatusLabel()" /></td><td class="text-right"><a href="{{ route('inventory.show', $itemBranch) }}" class="inline-flex rounded-md p-1.5 text-slate-500 hover:bg-slate-100 hover:text-brand-600" title="View item"><i data-lucide="eye" class="h-4 w-4"></i></a></td>
            </tr>
            @endforeach
        </tbody></table></div>
        <div class="border-t border-slate-200 px-5 py-4">{{ $inventory->links() }}</div>
        @else
        <x-empty-state icon="boxes" title="No inventory records found" message="Inventory appears here when STOCK items are received through Purchase Receipts." />
        @endif
    </section>
</x-app-layout>
