<x-app-layout title="Supplier Management">
    <x-page-header title="Suppliers" description="Maintain approved supplier records for purchasing."><x-slot name="actions"><a href="{{ route('admin.suppliers.create') }}" class="btn btn-primary"><i data-lucide="plus" class="h-4 w-4"></i></a></x-slot></x-page-header>
    <section class="card overflow-hidden">
        <form class="border-b border-slate-200 p-4">
            <div class="flex gap-3"><input class="input max-w-md" name="search" value="{{ $search }}" placeholder="Search supplier name or contact"><button class="btn btn-secondary">Search</button></div>
        </form>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Supplier ID</th>
                        <th>Supplier name</th>
                        <th>Address</th>
                        <th>Contact</th>
                        <th>Payment term</th>
                        <th>Currency</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>@forelse($suppliers as $supplier)<tr>
                        <td class="font-mono text-xs">#{{ $supplier->id }}</td>
                        <td class="font-semibold text-slate-900">{{ $supplier->name }}</td>
                        <td>{{ $supplier->address ?: "—" }}</td>
                        <td>{{ $supplier->contact ?: "—" }}</td>
                        <td>{{ $supplier->payment_term }}</td>
                        <td>{{ $supplier->currency }}</td>
                        <td>
                            <div class="flex justify-end gap-2"><a class="btn btn-ghost !px-2.5 !py-1.5" href="{{ route('admin.suppliers.edit', $supplier) }}">Edit</a>
                                <form method="POST" action="{{ route('admin.suppliers.destroy', $supplier) }}" onsubmit="return confirm('Delete this supplier? This cannot be undone.');">@csrf @method("DELETE")<button class="btn btn-ghost !px-2.5 !py-1.5 text-rose-600">Delete</button></form>
                            </div>
                        </td>
                    </tr>@empty<tr>
                        <td colspan="7" class="py-10 text-center text-slate-500">No suppliers found.</td>
                    </tr>@endforelse</tbody>
            </table>
        </div>@if($suppliers->hasPages())<div class="border-t border-slate-200 px-4 py-3">{{ $suppliers->links() }}</div>@endif
    </section>
</x-app-layout>