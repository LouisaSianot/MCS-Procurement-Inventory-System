<x-app-layout title="Receive Purchase Order">
    <x-page-header title="Receive Purchase Order" description="Record delivered quantities. STOCK lines increase branch inventory; NON-STOCK lines do not.">
        <x-slot name="actions"><a href="{{ route('receiving.index') }}" class="btn btn-secondary">Back to Receipts</a></x-slot>
    </x-page-header>

    @if (! $selectedPurchaseOrder)
    <section class="card animate-fade-in"><div class="border-b border-slate-200 p-5"><h2 class="text-base font-semibold">Select a purchase order</h2><p class="mt-1 text-sm text-slate-500">Only ordered and partially received purchase orders can be received.</p></div>
        @if ($purchaseOrders->isEmpty())<x-empty-state icon="truck" title="No purchase orders are ready" message="An ordered purchase order is required before a receipt can be posted." />
        @else <div class="table-wrap"><table class="data-table"><thead><tr><th>PO #</th><th>Supplier</th><th>Branch</th><th>Type</th><th class="text-right">Action</th></tr></thead><tbody>@foreach ($purchaseOrders as $purchaseOrder)<tr><td class="font-mono text-xs font-semibold">{{ $purchaseOrder->po_number }}</td><td>{{ $purchaseOrder->supplier->name }}</td><td>{{ $purchaseOrder->branch->name }}</td><td><x-status-badge :status="$purchaseOrder->geOrder->inventory_flag" /></td><td class="text-right"><a href="{{ route('receiving.create', ['purchase_order_id' => $purchaseOrder->id]) }}" class="btn btn-primary py-2">Select</a></td></tr>@endforeach</tbody></table></div>@endif
    </section>
    @else
    @php $isStock = $selectedPurchaseOrder->geOrder->inventory_flag === App\Models\GEOrder::INVENTORY_FLAG_STOCK; @endphp
    <form method="POST" action="{{ route('receiving.store') }}">@csrf
        <input type="hidden" name="purchase_order_id" value="{{ $selectedPurchaseOrder->id }}">
        <section class="card animate-fade-in"><div class="border-b border-slate-200 p-5"><h2 class="text-base font-semibold">Receipt details</h2></div><div class="grid grid-cols-1 gap-5 p-5 sm:grid-cols-2 lg:grid-cols-3">
            <x-form-field name="receipt_number" label="Receipt Number" :value="old('receipt_number', $receiptNumber)" :errors="$errors" required />
            <x-form-field name="received_at" label="Received Date" type="date" :value="old('received_at', $defaultDate)" :errors="$errors" required />
            <x-form-field name="supplier_delivery_reference" label="Supplier Delivery Reference" :value="old('supplier_delivery_reference')" :errors="$errors" />
            <div><p class="text-sm font-medium text-slate-700">Purchase Order</p><p class="mt-1.5 rounded-lg bg-slate-50 px-3.5 py-2.5 font-mono text-sm">{{ $selectedPurchaseOrder->po_number }}</p></div>
            <div><p class="text-sm font-medium text-slate-700">Inventory handling</p><p class="mt-1.5 rounded-lg bg-slate-50 px-3.5 py-2.5 text-sm">{{ $isStock ? 'STOCK — inventory will be updated' : 'NON-STOCK — no inventory update' }}</p></div>
            <div class="sm:col-span-2 lg:col-span-3"><x-form-field name="notes" label="Notes" type="textarea" :value="old('notes')" :errors="$errors" /></div>
        </div></section>
        <section class="card animate-fade-in mt-6"><div class="border-b border-slate-200 p-5"><h2 class="text-base font-semibold">Delivered line items</h2><p class="mt-1 text-sm text-slate-500">Enter only quantities delivered in this receipt.</p></div><div class="table-wrap"><table class="data-table"><thead><tr><th>Item</th><th>Ordered</th><th>Previously received</th><th>Outstanding</th><th>Receive now</th><th>Unit cost</th></tr></thead><tbody>
            @foreach ($selectedPurchaseOrder->items as $index => $line)
            @php $received = (float) $line->receiptItems->sum('quantity_received'); $outstanding = (float) $line->quantity - $received; @endphp
            @if ($outstanding > 0)
            <tr><td><input type="hidden" name="items[{{ $index }}][purchase_order_item_id]" value="{{ $line->id }}"><span class="font-medium">{{ $line->description }}</span><span class="ml-1 text-xs text-slate-500">{{ $line->unit }}</span></td><td>{{ $line->quantity }}</td><td>{{ number_format($received, 2) }}</td><td>{{ number_format($outstanding, 2) }}</td><td><input class="input w-28 py-2" name="items[{{ $index }}][quantity_received]" type="number" min="0.01" max="{{ $outstanding }}" step="0.01" value="{{ old("items.$index.quantity_received", $outstanding) }}" required></td><td><input class="input w-32 py-2" name="items[{{ $index }}][unit_cost]" type="number" min="0" step="0.01" value="{{ old("items.$index.unit_cost", $line->unit_price) }}" required></td></tr>
            @endif
            @endforeach
        </tbody></table></div></section>
        <div class="mt-6 flex justify-end gap-3"><a href="{{ route('receiving.create') }}" class="btn btn-secondary">Choose another PO</a><button class="btn btn-primary"><i data-lucide="package-check" class="h-4 w-4"></i> Post Receipt</button></div>
    </form>
    @endif
</x-app-layout>
