<x-app-layout title="Record Stock Issue">
    <x-page-header title="Record stock issue" description="Current Stock is decreased only after all references validate." />
    <form method="POST" action="{{ route('issues.store') }}" class="card max-w-3xl p-6">@csrf
        <div class="grid gap-5 sm:grid-cols-2">
            <x-form-field name="customer_id" label="CustomerID" type="select" :errors="$errors" required><option value="">Select customer</option>@foreach($customers as $customer)<option value="{{ $customer->id }}" @selected(old('customer_id') == $customer->id)>{{ $customer->id }} — {{ $customer->customer }}</option>@endforeach</x-form-field>
            <x-form-field name="branch_id" label="BranchID" type="select" :errors="$errors" required><option value="">Select branch</option>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected(old('branch_id') == $branch->id)>{{ $branch->id }} — {{ $branch->name }}</option>@endforeach</x-form-field>
            <x-form-field name="item_id" label="ItemID" type="select" :errors="$errors" required><option value="">Select item</option>@foreach($items as $item)<option value="{{ $item->id }}" @selected(old('item_id') == $item->id)>{{ $item->id }} — {{ $item->description }}</option>@endforeach</x-form-field>
            <x-form-field name="quantity" label="Quantity" type="number" :value="old('quantity')" :errors="$errors" required />
            <x-form-field name="uom" label="UOM" :value="old('uom')" :errors="$errors" required />
            <x-form-field name="date" label="Date" type="date" :value="old('date', now()->format('Y-m-d'))" :errors="$errors" required />
            <x-form-field name="purpose" label="Purpose" type="textarea" :value="old('purpose')" :errors="$errors" :col-span="2" required />
        </div><div class="mt-6 flex justify-end gap-3"><a href="{{ route('issues.index') }}" class="btn btn-secondary">Cancel</a><button class="btn btn-primary">Record issue</button></div>
    </form>
</x-app-layout>
