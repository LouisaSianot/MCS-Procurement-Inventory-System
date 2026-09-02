<x-app-layout title="Purchase Orders">
    @php
        $filters = $filters ?? [];
        $search = $filters['search'] ?? request('search');
        $status = $filters['status'] ?? request('status');
        $cards = [
            ['label' => 'All Purchase Orders', 'value' => $statusCounts['all'] ?? 0, 'icon' => 'shopping-cart', 'color' => 'brand', 'status' => null],
            ['label' => 'Drafts', 'value' => $statusCounts['draft'] ?? 0, 'icon' => 'file-edit', 'color' => 'slate', 'status' => 'draft'],
            ['label' => 'Ordered', 'value' => $statusCounts['ordered'] ?? 0, 'icon' => 'truck', 'color' => 'sky', 'status' => 'ordered'],
            ['label' => 'Backorders', 'value' => $statusCounts['backorder'] ?? 0, 'icon' => 'clock', 'color' => 'amber', 'status' => 'backorder'],
            ['label' => 'Fully Received', 'value' => $statusCounts['received'] ?? 0, 'icon' => 'package-check', 'color' => 'emerald', 'status' => 'fully received'],
        ];
        $colorMap = ['brand' => 'bg-brand-50 text-brand-600', 'slate' => 'bg-slate-100 text-slate-500', 'sky' => 'bg-sky-50 text-sky-600', 'amber' => 'bg-amber-50 text-amber-600', 'emerald' => 'bg-emerald-50 text-emerald-600'];
    @endphp

    <x-page-header title="Purchase Orders" description="Create and track supplier purchase orders from approved GE Orders.">
        <x-slot name="actions">
            @can('create', App\Models\PurchaseOrder::class)
            <a href="{{ route('procurement.create') }}" class="btn btn-primary">
                <i data-lucide="plus" class="h-4 w-4"></i> Create Purchase Order
            </a>
            @endcan
        </x-slot>
    </x-page-header>

    <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-5">
        @foreach ($cards as $card)
        <a href="{{ $card['status'] ? route('procurement.index', ['status' => $card['status']]) : route('procurement.index') }}" class="card card-hover animate-fade-in flex items-center gap-4 p-4 {{ $status === $card['status'] ? 'ring-2 ring-brand-500/40' : '' }}">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg {{ $colorMap[$card['color']] }}"><i data-lucide="{{ $card['icon'] }}" class="h-5 w-5"></i></span>
            <div><p class="text-xs font-medium text-slate-500">{{ $card['label'] }}</p><p class="text-xl font-bold tracking-tight text-slate-900">{{ $card['value'] }}</p></div>
        </a>
        @endforeach
    </div>

    <section class="card animate-fade-in">
        <form method="GET" action="{{ route('procurement.index') }}" class="border-b border-slate-200 p-5">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                <div class="relative"><i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"></i><input class="input pl-9" type="search" name="search" value="{{ $search }}" placeholder="Search PO #, GE #, supplier…"></div>
                <select name="status" class="input"><option value="">All statuses</option>@foreach (['draft' => 'Draft', 'ordered' => 'Ordered', 'backorder' => 'Backorder', 'partially received' => 'Partially Received', 'fully received' => 'Fully Received', 'cancelled' => 'Cancelled'] as $value => $label)<option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>@endforeach</select>
                <div class="flex justify-end gap-2"><a href="{{ route('procurement.index') }}" class="btn btn-ghost py-2"><i data-lucide="rotate-ccw" class="h-4 w-4"></i> Clear</a><button class="btn btn-primary py-2"><i data-lucide="filter" class="h-4 w-4"></i> Apply</button></div>
            </div>
        </form>

        @if ($orders->count())
        <div class="table-wrap"><table class="data-table hidden md:table"><thead><tr><th>PO #</th><th>GE Order</th><th>Supplier</th><th>Date</th><th class="text-right">Amount</th><th>Status</th><th class="text-right">Actions</th></tr></thead><tbody>
            @foreach ($orders as $purchaseOrder)
            <tr><td><a href="{{ route('procurement.show', $purchaseOrder) }}" class="font-mono text-xs font-semibold text-brand-600 hover:text-brand-700">{{ $purchaseOrder->po_number }}</a></td><td><a href="{{ route('ge-orders.show', $purchaseOrder->geOrder) }}" class="font-mono text-xs text-slate-600 hover:text-brand-600">{{ $purchaseOrder->geOrder->order_number }}</a></td><td>{{ $purchaseOrder->supplier->name }}</td><td class="text-slate-500">{{ $purchaseOrder->order_date->format('d M Y') }}</td><td class="text-right font-medium tabular-nums">K {{ number_format((float) $purchaseOrder->total_amount, 2) }}</td><td><x-status-badge :status="$purchaseOrder->status" /></td><td class="text-right"><a href="{{ route('procurement.show', $purchaseOrder) }}" class="inline-flex items-center justify-center rounded-md p-1.5 text-slate-500 hover:bg-slate-100 hover:text-brand-600" title="View"><i data-lucide="eye" class="h-4 w-4"></i></a></td></tr>
            @endforeach
        </tbody></table>
        <div class="divide-y divide-slate-100 md:hidden">@foreach ($orders as $purchaseOrder)<div class="p-4"><div class="flex items-start justify-between gap-3"><div><a href="{{ route('procurement.show', $purchaseOrder) }}" class="font-mono text-sm font-semibold text-brand-600">{{ $purchaseOrder->po_number }}</a><p class="mt-0.5 text-xs text-slate-500">{{ $purchaseOrder->supplier->name }} · {{ $purchaseOrder->geOrder->order_number }}</p></div><x-status-badge :status="$purchaseOrder->status" /></div><p class="mt-3 text-sm font-semibold tabular-nums text-slate-900">K {{ number_format((float) $purchaseOrder->total_amount, 2) }}</p></div>@endforeach</div></div>
        <div class="border-t border-slate-200 px-5 py-4">{{ $orders->links() }}</div>
        @else
        <x-empty-state icon="shopping-cart" title="No Purchase Orders yet" message="Create a Purchase Order from an approved GE Order to begin procurement."><x-slot name="actions">@can('create', App\Models\PurchaseOrder::class)<a href="{{ route('procurement.create') }}" class="btn btn-primary">Create Purchase Order</a>@endcan</x-slot></x-empty-state>
        @endif
    </section>
</x-app-layout>
