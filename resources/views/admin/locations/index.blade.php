<x-app-layout title="Locations">
    <x-page-header title="Locations" description="Physical inventory locations grouped by branch.">
        @can('create', App\Models\Location::class)
        <x-slot name="actions"><a href="{{ route('admin.locations.create') }}" class="btn btn-primary"><i data-lucide="plus" class="h-4 w-4"></i> Add location</a></x-slot>
        @endcan
    </x-page-header>

    <section class="card overflow-hidden">
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Branch</th><th>Location</th><th>Description</th><th>Stock records</th><th class="text-right">Actions</th></tr></thead>
                <tbody>
                    @forelse($locations as $location)
                    <tr>
                        <td>{{ $location->branch->name }}</td>
                        <td class="font-semibold text-slate-900">{{ $location->name }}</td>
                        <td>{{ $location->description ?: '—' }}</td>
                        <td>{{ $location->item_branches_count }}</td>
                        <td>
                            <div class="flex justify-end gap-2">
                                @can('update', $location)<a href="{{ route('admin.locations.edit', $location) }}" class="btn btn-ghost !px-2.5 !py-1.5">Edit</a>@endcan
                                @can('delete', $location)<form method="POST" action="{{ route('admin.locations.destroy', $location) }}" onsubmit="return confirm('Delete this location? Locations used by stock or movement history cannot be deleted.');">@csrf @method('DELETE')<button class="btn btn-ghost !px-2.5 !py-1.5 text-rose-600">Delete</button></form>@endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="py-10 text-center text-slate-500">No locations have been configured.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($locations->hasPages())<div class="border-t border-slate-200 px-4 py-3">{{ $locations->links() }}</div>@endif
    </section>
</x-app-layout>
