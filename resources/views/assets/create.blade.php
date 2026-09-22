<x-app-layout title="Register Asset">
    <x-page-header title="Register asset" description="An Asset requires an existing ItemID + BranchID ItemBranch record." />
    <form method="POST" action="{{ route('assets.store') }}" class="card max-w-4xl p-6">@csrf
        <div class="grid gap-5 sm:grid-cols-2">
            <x-form-field name="asset" label="Asset" :value="old('asset')" :errors="$errors" required />
            <x-form-field name="serial_number" label="Serial Number" :value="old('serial_number')" :errors="$errors" required />
            <x-form-field name="brand" label="Brand" :value="old('brand')" :errors="$errors" required />
            <x-form-field name="model" label="Model" :value="old('model')" :errors="$errors" required />
            <x-form-field name="item_id" label="ItemID" type="select" :errors="$errors" required><option value="">Select item</option>@foreach($items as $item)<option value="{{ $item->id }}">{{ $item->id }} — {{ $item->description }}</option>@endforeach</x-form-field>
            <x-form-field name="branch_id" label="BranchID" type="select" :errors="$errors" required><option value="">Select branch</option>@foreach($branches as $branch)<option value="{{ $branch->id }}">{{ $branch->id }} — {{ $branch->name }}</option>@endforeach</x-form-field>
            <x-form-field name="date" label="Date" type="date" :value="old('date', now()->format('Y-m-d'))" :errors="$errors" required />
            <x-form-field name="unit_cost" label="Unit Cost" type="number" :value="old('unit_cost')" :errors="$errors" required />
            <x-form-field name="status" label="Status" type="select" :errors="$errors" required><option value="">Select status</option>@foreach(\App\Models\Asset::STATUSES as $status)<option value="{{ $status }}">{{ $status }}</option>@endforeach</x-form-field>
            <x-form-field name="po_number" label="PONumber" :value="old('po_number')" :errors="$errors" required />
            <x-form-field name="location" label="Location" :value="old('location')" :errors="$errors" />
        </div><div class="mt-6 flex justify-end gap-3"><a href="{{ route('assets.index') }}" class="btn btn-secondary">Cancel</a><button class="btn btn-primary">Register asset</button></div>
    </form>
</x-app-layout>
