@php
    /*
    |-------------------------------------------------------------
    | Line item row — partial included inside the order items table.
    | Used by create.blade.php and edit.blade.php.
    | Variables: $i (index), $item (array of old values), $items (collection)
    |------------------------------------------------------------- */
    $isStock = (old('inventory_flag', $inventoryFlag ?? 'STOCK')) === 'STOCK';
    $item = $item ?? [];
@endphp

<tr data-item-row>
    <td>
        @if ($isStock)
            <select name="items[{{ $i }}][item_id]" data-field="item_id" class="input py-2 item-select" required>
                <option value="">Select item…</option>
                @foreach ($items as $inv)
                    <option value="{{ $inv->id }}" data-uom="{{ $inv->uom }}"
                        @if((string)($item['item_id'] ?? '') === (string)$inv->id) selected @endif>
                        {{ $inv->name }}
                    </option>
                @endforeach
            </select>
        @else
            <input type="text" name="items[{{ $i }}][description]" data-field="description"
                   value="{{ $item['description'] ?? '' }}" placeholder="Item description"
                   class="input py-2" required>
        @endif
    </td>
    <td>
        <input type="text" name="items[{{ $i }}][item_id_text]" data-field="item_id_text"
               value="{{ $item['item_id'] ?? '' }}" placeholder="—"
               class="input py-2 w-24 {{ $isStock ? 'bg-slate-50' : '' }}" {{ $isStock ? 'readonly' : '' }}>
    </td>
    <td>
        <input type="text" name="items[{{ $i }}][unit]" data-field="unit"
               value="{{ $item['unit'] ?? '' }}" placeholder="unit"
               class="input py-2 w-24">
    </td>
    <td class="text-right">
        <input type="number" name="items[{{ $i }}][quantity]" data-field="quantity"
               value="{{ $item['quantity'] ?? '' }}" min="0" step="0.01" placeholder="0"
               class="input py-2 w-24 text-right tabular-nums" required>
    </td>
    <td class="text-right">
        <input type="number" name="items[{{ $i }}][unit_price]" data-field="unit_price"
               value="{{ $item['unit_price'] ?? '' }}" min="0" step="0.01" placeholder="0.00"
               class="input py-2 w-28 text-right tabular-nums" required>
    </td>
    <td class="text-right">
        @php
            $rowTotal = (float)($item['quantity'] ?? 0) * (float)($item['unit_price'] ?? 0);
        @endphp
        <span data-field="total_display" class="font-medium tabular-nums text-slate-700">
            K {{ number_format($rowTotal, 2) }}
        </span>
        <input type="hidden" name="items[{{ $i }}][total]" data-field="total"
               value="{{ number_format($rowTotal, 2, '.', '') }}">
    </td>
    <td class="text-right">
        <button type="button" data-remove-row
                class="inline-flex items-center justify-center rounded-md p-1.5 text-slate-400 hover:bg-rose-50 hover:text-rose-600">
            <i data-lucide="trash-2" class="h-4 w-4"></i>
        </button>
    </td>
</tr>
