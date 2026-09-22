<x-app-layout title="Record Stock Adjustment">
    <x-page-header title="Record stock adjustment" description="Adjust-IN increases stock; Adjust-OUT decreases it." />
    <form method="POST" action="{{ route('adjustments.store') }}" class="card max-w-3xl p-6">@csrf
        <div class="grid gap-5 sm:grid-cols-2">
            <x-form-field name="adjustment_type" label="AdjustmentType" type="select" :errors="$errors" required><option value="">Select type</option>@foreach(\App\Models\Adjustment::TYPES as $type)<option value="{{ $type }}" @selected(old('adjustment_type') === $type)>{{ $type }}</option>@endforeach</x-form-field>
            <x-form-field name="branch_id" label="BranchID" type="select" :errors="$errors" required><option value="">Select branch</option>@foreach($branches as $branch)<option value="{{ $branch->id }}">{{ $branch->id }} — {{ $branch->name }}</option>@endforeach</x-form-field>
            <x-form-field name="item_id" label="ItemID" type="select" :errors="$errors" required><option value="">Select item</option>@foreach($items as $item)<option value="{{ $item->id }}">{{ $item->id }} — {{ $item->description }}</option>@endforeach</x-form-field>
            <x-form-field name="quantity" label="Quantity" type="number" :value="old('quantity')" :errors="$errors" required />
            <x-form-field name="uom" label="UOM" :value="old('uom')" :errors="$errors" required />
            <x-form-field name="date" label="Date" type="date" :value="old('date', now()->format('Y-m-d'))" :errors="$errors" required />
            <x-form-field name="purpose" label="Purpose" type="textarea" :value="old('purpose')" :errors="$errors" :col-span="2" required />
        </div><div class="mt-6 flex justify-end gap-3"><a href="{{ route('adjustments.index') }}" class="btn btn-secondary">Cancel</a><button class="btn btn-primary">Record adjustment</button></div>
    </form>
</x-app-layout>
