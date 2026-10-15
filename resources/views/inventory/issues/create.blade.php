<x-app-layout title="Issue Inventory">
    <x-page-header title="Issue inventory" description="{{ $itemBranch->item->description }} · {{ $itemBranch->locationRecord->branch->name }} · {{ $itemBranch->locationRecord->name }}" :breadcrumbs="[['label' => 'Inventory', 'url' => route('inventory.index')], ['label' => $itemBranch->item->description, 'url' => route('inventory.show', $itemBranch)], ['label' => 'Issue inventory']]">
        <x-slot name="actions"><a href="{{ route('inventory.show', $itemBranch) }}" class="btn btn-secondary"><i data-lucide="arrow-left" class="h-4 w-4"></i> Back to item</a></x-slot>
    </x-page-header>

    <form method="POST" action="{{ route('inventory.issues.store', $itemBranch) }}" class="card max-w-3xl p-6">
        @csrf
        <div class="mb-5 flex items-baseline justify-between border-b border-slate-200 pb-4">
            <h2 class="text-base font-semibold text-slate-900">Available stock</h2>
            <span class="text-sm tabular-nums text-slate-600">{{ number_format((float) $itemBranch->current_stock, 2) }} {{ $itemBranch->uom ?? $itemBranch->item->uom }}</span>
        </div>
        <div class="grid gap-5 sm:grid-cols-2">
            <x-form-field name="type" label="Issue type" type="select" :errors="$errors" required>
                <option value="consumable" @selected(old('type')==='consumable' )>Consumable</option>
                <option value="checkout" @selected(old('type')==='checkout' )>Checkout, return expected</option>
            </x-form-field>
            <x-form-field name="quantity" label="Quantity" type="number" :value="old('quantity')" :errors="$errors" min="0.01" step="0.01" max="{{ $itemBranch->current_stock }}" required />
            <x-form-field name="recipient_user_id" label="Recipient account" type="select" :errors="$errors">
                <option value="">No linked account</option>
                @foreach($users as $user)
                <option value="{{ $user->id }}" @selected((string) old('recipient_user_id')===(string) $user->id)>{{ $user->name }} · {{ $user->email }}</option>
                @endforeach
            </x-form-field>
            <x-form-field name="recipient_name" label="Or recipient name" :value="old('recipient_name')" :errors="$errors" maxlength="255" />
            <div class="sm:col-span-2">
                <x-form-field name="purpose" label="Purpose" :value="old('purpose')" :errors="$errors" maxlength="2000" required />
            </div>
        </div>

        @if($itemBranch->item->is_serialized)
        <fieldset class="mt-6 border-t border-slate-200 pt-5">
            <legend class="text-sm font-semibold text-slate-900">Select serialized units</legend>
            <p class="mt-1 text-xs text-slate-500">Select the exact units. Quantity will follow the selection.</p>
            @if($serials->isNotEmpty())
            <div class="mt-3 grid gap-2 sm:grid-cols-2">
                @foreach($serials as $serial)
                <label class="flex items-center gap-3 border border-slate-200 px-3 py-2 text-sm">
                    <input type="checkbox" name="serial_ids[]" value="{{ $serial->id }}" class="issue-serial rounded border-slate-300 text-brand-600" @checked(in_array($serial->id, old('serial_ids', [])))>
                    <span class="font-mono">{{ $serial->serial_number }}</span>
                </label>
                @endforeach
            </div>
            @else
            <p class="mt-3 text-sm text-amber-700">No active serialized units are available at this location.</p>
            @endif
            @error('serial_ids')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
        </fieldset>
        @endif

        @error('type')<p class="mt-3 text-sm text-rose-600">{{ $message }}</p>@enderror
        @error('recipient_user_id')<p class="mt-3 text-sm text-rose-600">{{ $message }}</p>@enderror
        @error('recipient_name')<p class="mt-3 text-sm text-rose-600">{{ $message }}</p>@enderror
        @error('purpose')<p class="mt-3 text-sm text-rose-600">{{ $message }}</p>@enderror
        <div class="mt-6 flex justify-end gap-3">
            <a href="{{ route('inventory.show', $itemBranch) }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary"><i data-lucide="package-minus" class="h-4 w-4"></i> Confirm issue</button>
        </div>
    </form>

    @if($itemBranch->item->is_serialized)
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const quantity = document.querySelector('[name="quantity"]');
            const serialInputs = Array.from(document.querySelectorAll('.issue-serial'));
            const updateQuantity = () => {
                const selectedCount = serialInputs.filter((input) => input.checked).length;
                quantity.value = selectedCount || '';
                quantity.readOnly = true;
            };
            serialInputs.forEach((input) => input.addEventListener('change', updateQuantity));
            updateQuantity();
        });
    </script>
    @endif
</x-app-layout>