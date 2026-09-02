<x-app-layout title="Purchase Receipts">
    <x-page-header title="Purchase Receipts" description="Record supplier deliveries and post stock receipts.">
        <x-slot name="actions">
            @can('create', App\Models\PurchaseReceipt::class)
            <a href="{{ route('receiving.create') }}" class="btn btn-primary"><i data-lucide="package-check" class="h-4 w-4"></i> Receive Purchase</a>
            @endcan
        </x-slot>
    </x-page-header>

    <section class="card animate-fade-in">
        @if ($receipts->isNotEmpty())
        <div class="table-wrap"><table class="data-table"><thead><tr><th>Receipt #</th><th>Purchase Order</th><th>Supplier</th><th>Received date</th><th>Received by</th><th class="text-right">Action</th></tr></thead><tbody>
            @foreach ($receipts as $receipt)
            <tr><td><a href="{{ route('receiving.show', $receipt) }}" class="font-mono text-xs font-semibold text-brand-600">{{ $receipt->receipt_number }}</a></td><td>{{ $receipt->purchaseOrder->po_number }}</td><td>{{ $receipt->purchaseOrder->supplier->name }}</td><td>{{ $receipt->received_at->format('d M Y') }}</td><td>{{ $receipt->receiver->name }}</td><td class="text-right"><a href="{{ route('receiving.show', $receipt) }}" class="text-sm font-medium text-brand-600">View</a></td></tr>
            @endforeach
        </tbody></table></div>
        <div class="border-t border-slate-200 px-5 py-4">{{ $receipts->links() }}</div>
        @else
        <x-empty-state icon="package-check" title="No purchase receipts yet" message="Receive an ordered purchase order to record delivery and update stock."><x-slot name="actions"><a href="{{ route('receiving.create') }}" class="btn btn-primary">Receive Purchase</a></x-slot></x-empty-state>
        @endif
    </section>
</x-app-layout>
