<x-app-layout title="Stock Issues">
    <x-page-header title="Stock Issues" description="Issue stock to a valid customer from an ItemBranch balance.">
        <x-slot name="actions"><a href="{{ route('issues.create') }}" class="btn btn-primary">Record issue</a></x-slot>
    </x-page-header>
    <section class="card overflow-hidden"><div class="table-wrap"><table class="data-table"><thead><tr><th>IssueNumber</th><th>Date</th><th>Customer</th><th>Item</th><th>Branch</th><th class="text-right">Quantity</th><th>Purpose</th></tr></thead><tbody>
    @forelse($issues as $issue)<tr><td class="font-mono">#{{ $issue->issue_number }}</td><td>{{ $issue->date->format('Y-m-d') }}</td><td>{{ $issue->customer->customer }}</td><td>{{ $issue->item->description }}</td><td>{{ $issue->branch->name }}</td><td class="text-right">{{ $issue->quantity }} {{ $issue->uom }}</td><td>{{ $issue->purpose }}</td></tr>@empty<tr><td colspan="7" class="py-10 text-center text-slate-500">No stock issues recorded.</td></tr>@endforelse</tbody></table></div>{{ $issues->links() }}</section>
</x-app-layout>
