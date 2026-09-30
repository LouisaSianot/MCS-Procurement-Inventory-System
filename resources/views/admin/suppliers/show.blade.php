<x-app-layout title="Supplier Details">
    <x-page-header title="{{ $supplier->name }}" description="Supplier details and related order line items.">
        <x-slot name="actions">
            <a href="{{ route('admin.suppliers.index') }}" class="btn btn-secondary">Back to suppliers</a>
            <a href="{{ route('admin.suppliers.edit', $supplier) }}" class="btn btn-primary">Edit supplier</a>
        </x-slot>
    </x-page-header>

    <section class="card mb-6 p-5">
        <dl class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div><dt class="text-xs font-semibold uppercase text-slate-500">Supplier ID</dt><dd class="mt-1 font-mono text-sm">#{{ $supplier->id }}</dd></div>
            <div><dt class="text-xs font-semibold uppercase text-slate-500">Address</dt><dd class="mt-1 text-sm">{{ $supplier->address ?: '—' }}</dd></div>
            <div><dt class="text-xs font-semibold uppercase text-slate-500">Contact</dt><dd class="mt-1 text-sm">{{ $supplier->contact ?: '—' }}</dd></div>
            <div><dt class="text-xs font-semibold uppercase text-slate-500">Payment term</dt><dd class="mt-1 text-sm">{{ $supplier->payment_term ?: '—' }}</dd></div>
            <div><dt class="text-xs font-semibold uppercase text-slate-500">Currency</dt><dd class="mt-1 text-sm">{{ $supplier->currency ?: '—' }}</dd></div>
        </dl>
    </section>

    <section class="card overflow-hidden">
        <div class="border-b border-slate-200 px-4 py-3">
            <h2 class="text-base font-semibold text-slate-900">Related Order Line Items</h2>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Description</th>
                        <th>Quantity</th>
                        <th>GE Order Number</th>
                        <th>PO Number</th>
                        <th>Order Status</th>
                        <th>Relevant Dates</th>
                    </tr>
                </thead>
                <tbody>
                    @php($hasLines = false)
                    @foreach($orders as $order)
                        @foreach($order->items as $line)
                            @php($hasLines = true)
                            <tr>
                                <td>{{ $line->item?->description ?? '—' }}</td>
                                <td>{{ $line->description ?: '—' }}</td>
                                <td class="tabular-nums">{{ $line->quantity }}</td>
                                <td class="font-mono text-xs">{{ $order->order_number ?: '—' }}</td>
                                <td>
                                    @if($order->purchase_order_visible)
                                        {{ $order->purchaseOrder->po_number ?: '—' }}
                                    @elseif($order->has_purchase_order)
                                        Restricted
                                    @else
                                        Not yet created
                                    @endif
                                </td>
                                <td>
                                    <div class="flex flex-col items-start gap-1">
                                        <div class="flex items-center gap-1.5"><span class="text-xs text-slate-500">GE</span><x-status-badge :status="$order->status" /></div>
                                        @if($order->purchase_order_visible)
                                            <div class="flex items-center gap-1.5"><span class="text-xs text-slate-500">PO</span><x-status-badge :status="$order->purchaseOrder->status" /></div>
                                        @endif
                                    </div>
                                </td>
                                <td class="whitespace-nowrap text-slate-500">
                                    <div>GE: {{ $order->order_date?->format('d M Y') ?? '—' }}</div>
                                    @if($order->purchase_order_visible)
                                        <div>PO: {{ $order->purchaseOrder->order_date?->format('d M Y') ?? '—' }}</div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                    @if(! $hasLines)
                        <tr><td colspan="7" class="py-10 text-center text-slate-500">No related order line items found.</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </section>
</x-app-layout>
