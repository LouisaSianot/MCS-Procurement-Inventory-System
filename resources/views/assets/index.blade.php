<x-app-layout title="Asset Register">
    <x-page-header title="Asset Register" description="Register STOCK items against their ItemBranch holding."><x-slot name="actions"><a href="{{ route('assets.create') }}" class="btn btn-primary">Register asset</a></x-slot></x-page-header>
    <section class="card overflow-hidden"><div class="table-wrap"><table class="data-table"><thead><tr><th>AssetID</th><th>Asset</th><th>Serial Number</th><th>Item</th><th>Branch</th><th>Status</th><th>PONumber</th></tr></thead><tbody>
    @forelse($assets as $asset)<tr><td class="font-mono">#{{ $asset->asset_id }}</td><td>{{ $asset->asset }}</td><td>{{ $asset->serial_number }}</td><td>{{ $asset->item->description }}</td><td>{{ $asset->branch->name }}</td><td>{{ $asset->status }}</td><td>{{ $asset->po_number }}</td></tr>@empty<tr><td colspan="7" class="py-10 text-center text-slate-500">No assets registered.</td></tr>@endforelse</tbody></table></div>{{ $assets->links() }}</section>
</x-app-layout>
