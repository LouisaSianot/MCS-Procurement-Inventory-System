<x-app-layout title="Customer Management">
    <x-page-header title="Customers" description="Maintain customers who may receive issued stock.">
        <x-slot name="actions"><a href="{{ route('admin.customers.create') }}" class="btn btn-primary">Add customer</a></x-slot>
    </x-page-header>
    <section class="card overflow-hidden">
        <form class="border-b border-slate-200 p-4"><div class="flex gap-3"><input class="input max-w-md" name="search" value="{{ $search }}" placeholder="Search customer or email"><button class="btn btn-secondary">Search</button></div></form>
        <div class="table-wrap"><table class="data-table"><thead><tr><th>CustomerID</th><th>Customer</th><th>CustomerType</th><th>Email</th><th class="text-right">Actions</th></tr></thead><tbody>
        @forelse($customers as $customer)<tr><td class="font-mono text-xs">#{{ $customer->customerID }}</td><td class="font-semibold">{{ $customer->customer }}</td><td>{{ $customer->customer_type }}</td><td>{{ $customer->email }}</td><td class="text-right"><a class="btn btn-ghost" href="{{ route('admin.customers.edit', $customer) }}">Edit</a><form class="inline" method="POST" action="{{ route('admin.customers.destroy', $customer) }}">@csrf @method('DELETE')<button class="btn btn-ghost text-rose-600">Delete</button></form></td></tr>
        @empty<tr><td colspan="5" class="py-10 text-center text-slate-500">No customers found.</td></tr>@endforelse</tbody></table></div>
        @if($customers->hasPages())<div class="border-t border-slate-200 px-4 py-3">{{ $customers->links() }}</div>@endif
    </section>
</x-app-layout>
