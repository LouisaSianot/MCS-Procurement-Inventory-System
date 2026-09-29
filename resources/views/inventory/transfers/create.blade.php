<x-app-layout title="Move Stock">
    <x-page-header title="Move stock" description="Transfer inventory between locations in the same branch." :breadcrumbs="[['label' => 'Inventory', 'url' => route('inventory.index')], ['label' => 'Move stock']]">
        <x-slot name="actions"><a href="{{ route('inventory.index') }}" class="btn btn-secondary"><i data-lucide="arrow-left" class="h-4 w-4"></i> Inventory</a></x-slot>
    </x-page-header>

    @if($stockRecords->isEmpty())
    <x-empty-state icon="arrow-left-right" title="No stock available to move" message="Stock transfers are available when inventory has been received into a location." />
    @else
    <form method="POST" action="{{ route('inventory.transfers.store') }}" class="card max-w-3xl p-6">
        @csrf
        <div class="grid gap-5 sm:grid-cols-2">
            <x-form-field name="item_id" label="Item" type="select" :errors="$errors" required>
                <option value="">Select an item</option>
                @foreach($items as $item)
                <option value="{{ $item->id }}" @selected((string) old('item_id', request('item_id')) === (string) $item->id)>{{ $item->description }} · #{{ $item->id }}</option>
                @endforeach
            </x-form-field>
            <x-form-field name="from_location_id" label="From location" type="select" :errors="$errors" required>
                <option value="">Select source</option>
                @foreach($stockRecords as $stockRecord)
                <option value="{{ $stockRecord->location_id }}" data-item="{{ $stockRecord->item_id }}" data-branch="{{ $stockRecord->branch_id }}" @selected((string) old('from_location_id') === (string) $stockRecord->location_id)>{{ $stockRecord->locationRecord->branch->name }} · {{ $stockRecord->locationRecord->name }} · {{ $stockRecord->current_stock }} available</option>
                @endforeach
            </x-form-field>
            <x-form-field name="to_location_id" label="To location" type="select" :errors="$errors" required>
                <option value="">Select destination</option>
                @foreach($locations as $location)
                <option value="{{ $location->id }}" data-branch="{{ $location->branch_id }}" @selected((string) old('to_location_id') === (string) $location->id)>{{ $location->branch->name }} · {{ $location->name }}</option>
                @endforeach
            </x-form-field>
            <x-form-field name="quantity" label="Quantity" type="number" :value="old('quantity')" :errors="$errors" min="0.01" step="0.01" required />
        </div>
        @error('item_id')<p class="mt-3 text-sm text-rose-600">{{ $message }}</p>@enderror
        <div class="mt-6 flex justify-end gap-3">
            <a href="{{ route('inventory.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary"><i data-lucide="arrow-left-right" class="h-4 w-4"></i> Confirm transfer</button>
        </div>
    </form>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const item = document.querySelector('[name="item_id"]');
            const source = document.querySelector('[name="from_location_id"]');
            const destination = document.querySelector('[name="to_location_id"]');
            const sourceOptions = Array.from(source.options).slice(1);
            const destinationOptions = Array.from(destination.options).slice(1);
            const refresh = () => {
                sourceOptions.forEach((option) => {
                    option.hidden = option.dataset.item !== item.value;
                    option.disabled = option.hidden;
                });
                if (source.selectedOptions[0]?.disabled) source.value = '';
                const branchId = source.selectedOptions[0]?.dataset.branch;
                destinationOptions.forEach((option) => {
                    option.hidden = !branchId || option.dataset.branch !== branchId || option.value === source.value;
                    option.disabled = option.hidden;
                });
                if (destination.selectedOptions[0]?.disabled) destination.value = '';
            };
            item.addEventListener('change', refresh);
            source.addEventListener('change', refresh);
            refresh();
        });
    </script>
    @endif
</x-app-layout>
