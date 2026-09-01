<x-app-layout title="Create Purchase Order">
    <x-page-header title="Create Purchase Order" description="Generate a supplier Purchase Order from an approved GE Order." :breadcrumbs="[['label' => 'Purchase Orders', 'url' => route('procurement.index')], ['label' => 'Create']]">
        <x-slot name="actions"><a href="{{ route('procurement.index') }}" class="btn btn-secondary"><i data-lucide="arrow-left" class="h-4 w-4"></i> Back to Purchase Orders</a></x-slot>
    </x-page-header>

    @if (! $selectedGEOrder)
    <section class="card animate-fade-in"><div class="border-b border-slate-200 p-5"><h3 class="text-base font-semibold text-slate-900">Select an approved GE Order</h3><p class="mt-0.5 text-sm text-slate-500">Each GE Order can have one Purchase Order.</p></div>
        @if ($eligibleOrders->isEmpty())<x-empty-state icon="file-check-2" title="No GE Orders are ready" message="An approved GE Order without a Purchase Order is required before procurement can begin." />
        @else <div class="table-wrap"><table class="data-table"><thead><tr><th>GE Order</th><th>Supplier</th><th>Description</th><th class="text-right">Amount</th><th class="text-right">Action</th></tr></thead><tbody>@foreach ($eligibleOrders as $geOrder)<tr><td class="font-mono text-xs font-semibold text-brand-600">{{ $geOrder->order_number }}</td><td>{{ $geOrder->supplier->name }}</td><td class="max-w-sm truncate text-slate-600">{{ $geOrder->description }}</td><td class="text-right font-medium tabular-nums">K {{ number_format((float) $geOrder->total_amount, 2) }}</td><td class="text-right"><a href="{{ route('procurement.create', ['ge_order_id' => $geOrder->id]) }}" class="btn btn-primary py-2">Select</a></td></tr>@endforeach</tbody></table></div>@endif
    </section>
    @else
    <form method="POST" action="{{ route('procurement.store') }}">@csrf<input type="hidden" name="ge_order_id" value="{{ $selectedGEOrder->id }}">
        <section class="card animate-fade-in"><div class="border-b border-slate-200 p-5"><h3 class="text-base font-semibold text-slate-900">Purchase Order details</h3><p class="mt-0.5 text-sm text-slate-500">Supplier, branch, and line items are copied from {{ $selectedGEOrder->order_number }}.</p></div><div class="grid grid-cols-1 gap-5 p-5 sm:grid-cols-2 lg:grid-cols-3">
            <x-form-field name="po_number" label="PO Number" :value="old('po_number', $poNumber)" :errors="$errors" required />
            <x-form-field name="order_date" label="Order Date" type="date" :value="old('order_date', $defaultDate)" :errors="$errors" required />
            <x-form-field name="expected_delivery_date" label="Expected Delivery" type="date" :value="old('expected_delivery_date')" :errors="$errors" />
            <div><p class="text-sm font-medium text-slate-700">Supplier</p><p class="mt-1.5 rounded-lg bg-slate-50 px-3.5 py-2.5 text-sm text-slate-700">{{ $selectedGEOrder->supplier->name }}</p></div>
            <div><p class="text-sm font-medium text-slate-700">Branch</p><p class="mt-1.5 rounded-lg bg-slate-50 px-3.5 py-2.5 text-sm text-slate-700">{{ $selectedGEOrder->branch->name }}</p></div>
            <div><p class="text-sm font-medium text-slate-700">GE Order</p><p class="mt-1.5 font-mono text-sm font-semibold text-brand-600">{{ $selectedGEOrder->order_number }}</p></div>
            <div class="sm:col-span-2 lg:col-span-3"><x-form-field name="notes" label="Notes" type="textarea" :value="old('notes')" :errors="$errors" placeholder="Supplier reference, delivery instructions, or purchasing notes" /></div>
        </div></section>
        <section class="card animate-fade-in mt-6"><div class="border-b border-slate-200 p-5"><h3 class="text-base font-semibold text-slate-900">Source line items</h3></div><div class="table-wrap"><table class="data-table"><thead><tr><th>Item</th><th>Unit</th><th class="text-right">Quantity</th><th class="text-right">Unit Price</th><th class="text-right">Total</th></tr></thead><tbody>@foreach ($selectedGEOrder->items as $item)<tr><td>{{ $item->description }}</td><td>{{ $item->unit ?? '—' }}</td><td class="text-right tabular-nums">{{ $item->quantity }}</td><td class="text-right tabular-nums">K {{ number_format((float) $item->unit_price, 2) }}</td><td class="text-right font-medium tabular-nums">K {{ number_format((float) $item->total, 2) }}</td></tr>@endforeach</tbody></table></div><div class="border-t border-slate-200 p-5 text-right"><p class="text-sm text-slate-500">Purchase Order Total</p><p class="mt-1 text-2xl font-bold text-slate-900">K {{ number_format((float) $selectedGEOrder->total_amount, 2) }}</p></div></section>
        <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end"><a href="{{ route('procurement.create') }}" class="btn btn-secondary">Choose another GE Order</a><button name="action" value="save_draft" class="btn btn-secondary"><i data-lucide="save" class="h-4 w-4"></i> Save Draft</button><button name="action" value="place_order" class="btn btn-primary"><i data-lucide="send" class="h-4 w-4"></i> Mark as Ordered</button></div>
    </form>
    @endif
</x-app-layout>
