<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Create GE Order</h2>
    </x-slot>

    @php
    /* Variables from GEOrderController::create():
    * $geNumber — pre-allocated GE number string (e.g. "GE-00130")
    * $suppliers — collection of Supplier (id => name)
    * $branches — collection of Branch / Department (id => name)
    * $accountCodes — collection of account codes
    * $items — collection of inventory items (for STOCK validation)
    * $users — collection of users for requester select
    * $errors — validation errors (also available via $errors global)
    *
    * All collections fall back to demo data so the view renders during development.
    */
    $geNumber = $geNumber ?? 'GE-' . str_pad((string) ((int) optional(\DB::table('ge_orders')->max('id'))->count() + 130), 5, '0', STR_PAD_LEFT);
    $suppliers = $suppliers ?? collect([
    (object)['id' => 1, 'name' => 'PNG Office Supplies'],
    (object)['id' => 2, 'name' => 'Tech Supplies Ltd'],
    (object)['id' => 3, 'name' => 'Office Gear Co'],
    (object)['id' => 4, 'name' => 'Medi Supplies PNG'],
    ]);
    $branches = $branches ?? collect([
    (object)['id' => 201, 'name' => '201 — Main Campus'],
    (object)['id' => 202, 'name' => '202 — Satellite Campus'],
    ]);
    $accountCodes = $accountCodes ?? collect([
    (object)['id' => 1, 'code' => '5001-Office Supplies'],
    (object)['id' => 2, 'code' => '5002-IT Supplies'],
    (object)['id' => 3, 'code' => '5003-Health & Safety'],
    (object)['id' => 4, 'code' => '5004-Maintenance'],
    ]);
    $items = $items ?? collect([
    (object)['id' => 1, 'name' => 'A4 Paper', 'uom' => 'ream'],
    (object)['id' => 2, 'name' => 'Printer Toner', 'uom' => 'unit'],
    (object)['id' => 3, 'name' => 'Blue Pens', 'uom' => 'box'],
    (object)['id' => 4, 'name' => 'USB-C Cables', 'uom' => 'unit'],
    ]);
    $users = $users ?? collect([
    (object)['id' => auth()->id() ?? 1, 'name' => auth()->user()?->name ?? 'Purchasing Officer'],
    ]);

    $defaultBranchId = $defaultBranchId ?? 201;
    $defaultDate = $defaultDate ?? now()->format('Y-m-d');
    $defaultUserId = $defaultUserId ?? auth()->id() ?? 1;

    // Persisted line items from old() on validation failure.
    $oldItems = array_filter(
    array_map(fn($i) => is_array($i) ? $i : null, old('items', [])),
    fn($i) => $i !== null && (! empty($i['description']) || ! empty($i['quantity']))
    );
    $oldItems = array_values($oldItems);
    if (empty($oldItems)) {
    $oldItems = [['description' => '', 'quantity' => '', 'unit' => '', 'unit_price' => '', 'total' => '']];
    }
    @endphp

    <x-page-header
        title="Create GE Order"
        description="Enter the order header and add line items as per quote."
        :breadcrumbs="[['label' => 'GE Orders', 'url' => route('ge-orders.index')], ['label' => 'Create']]" />

    {{-- Validation error summary --}}
    @if ($errors->any())
    <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 p-4">
        <div class="flex items-center gap-2">
            <i data-lucide="alert-circle" class="h-5 w-5 text-rose-600"></i>
            <h3 class="text-sm font-semibold text-rose-800">There were {{ $errors->count() }} problems with your submission</h3>
        </div>
        <ul class="mt-2 list-inside list-disc text-sm text-rose-700">
            @foreach ($errors->all() as $err)
            <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('ge-orders.store') }}" id="ge-order-form"
        data-inventory-items="{{ base64_encode(json_encode($items->map(fn($i) => ['id' => $i->id, 'name' => $i->name, 'uom' => $i->uom])->values()->all())) }}">
        @csrf

        {{-- Hidden action switch: save_draft | submit --}}
        <input type="hidden" name="action" id="form-action" value="save_draft">

        {{-- ===== Order header ===== --}}
        <section class="card animate-fade-in">
            <div class="border-b border-slate-200 p-5">
                <h3 class="text-base font-semibold text-slate-900">Order Details</h3>
                <p class="mt-0.5 text-sm text-slate-500">Header information for this General Expenditure order.</p>
            </div>
            <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2 lg:grid-cols-3">

                {{-- GE Number (pre-allocated, read-only) --}}
                <div>
                    <label for="order_number" class="block text-sm font-medium text-slate-700">GE Number</label>
                    <div class="mt-1.5">
                        <input id="order_number" type="text" name="order_number"
                            value="{{ old('order_number', $geNumber) }}" readonly
                            class="input cursor-not-allowed bg-slate-50 font-mono font-semibold text-slate-700">
                    </div>
                    <p class="mt-1 text-xs text-slate-400">Pre-allocated by the system.</p>
                </div>

                {{-- Inventory Flag --}}
                <div>
                    <label for="inventory_flag" class="block text-sm font-medium text-slate-700">Inventory Flag <span class="text-rose-500">*</span></label>
                    <select id="inventory_flag" name="inventory_flag" required class="input mt-1.5 {{ $errors->has('inventory_flag') ? 'border-rose-400' : '' }}">
                        @foreach (['STOCK' => 'STOCK — from inventory', 'NON-STOCK' => 'NON-STOCK — one-off purchase'] as $val => $lbl)
                        <option value="{{ $val }}" @if(old('inventory_flag')===$val) selected @endif>{{ $lbl }}</option>
                        @endforeach
                    </select>
                    @if ($errors->has('inventory_flag'))<p class="mt-1 text-xs font-medium text-rose-600">{{ $errors->first('inventory_flag') }}</p>@endif
                </div>

                {{-- PO Number (nullable) --}}
                <div>
                    <label for="po_number" class="block text-sm font-medium text-slate-700">PO Number</label>
                    <input id="po_number" type="text" name="po_number"
                        value="{{ old('po_number', $geNumber) }}" placeholder="Defaults to GE number"
                        class="input mt-1.5 {{ $errors->has('po_number') ? 'border-rose-400' : '' }}">
                    @if ($errors->has('po_number'))<p class="mt-1 text-xs font-medium text-rose-600">{{ $errors->first('po_number') }}</p>@endif
                </div>

                {{-- Supplier --}}
                <div>
                    <label for="supplier_id" class="block text-sm font-medium text-slate-700">Supplier <span class="text-rose-500">*</span></label>
                    <select id="supplier_id" name="supplier_id" required class="input mt-1.5 {{ $errors->has('supplier_id') ? 'border-rose-400' : '' }}">
                        <option value="">Select supplier…</option>
                        @foreach ($suppliers as $s)
                        <option value="{{ $s->id }}" @if(old('supplier_id')==$s->id) selected @endif>{{ $s->name }}</option>
                        @endforeach
                    </select>
                    @if ($errors->has('supplier_id'))<p class="mt-1 text-xs font-medium text-rose-600">{{ $errors->first('supplier_id') }}</p>@endif
                </div>

                {{-- Requester (Purchasing Officer) --}}
                <div>
                    <label for="user_id" class="block text-sm font-medium text-slate-700">Requester <span class="text-rose-500">*</span></label>
                    <select id="user_id" name="user_id" required class="input mt-1.5 {{ $errors->has('user_id') ? 'border-rose-400' : '' }}">
                        @foreach ($users as $u)
                        <option value="{{ $u->id }}" @if(old('user_id', $defaultUserId)==$u->id) selected @endif>{{ $u->name }}</option>
                        @endforeach
                    </select>
                    @if ($errors->has('user_id'))<p class="mt-1 text-xs font-medium text-rose-600">{{ $errors->first('user_id') }}</p>@endif
                </div>

                {{-- Date --}}
                <div>
                    <label for="order_date" class="block text-sm font-medium text-slate-700">Date <span class="text-rose-500">*</span></label>
                    <input id="order_date" type="date" name="order_date"
                        value="{{ old('order_date', $defaultDate) }}" required
                        class="input mt-1.5 {{ $errors->has('order_date') ? 'border-rose-400' : '' }}">
                    @if ($errors->has('order_date'))<p class="mt-1 text-xs font-medium text-rose-600">{{ $errors->first('order_date') }}</p>@endif
                </div>

                {{-- Branch ID --}}
                <div>
                    <label for="branch_id" class="block text-sm font-medium text-slate-700">Branch / Department <span class="text-rose-500">*</span></label>
                    <select id="branch_id" name="branch_id" required class="input mt-1.5 {{ $errors->has('branch_id') ? 'border-rose-400' : '' }}">
                        @foreach ($branches as $b)
                        <option value="{{ $b->id }}" @if(old('branch_id', $defaultBranchId)==$b->id) selected @endif>{{ $b->name }}</option>
                        @endforeach
                    </select>
                    @if ($errors->has('branch_id'))<p class="mt-1 text-xs font-medium text-rose-600">{{ $errors->first('branch_id') }}</p>@endif
                </div>

                {{-- Account Code --}}
                <div>
                    <label for="account_code" class="block text-sm font-medium text-slate-700">Account Code <span class="text-rose-500">*</span></label>
                    <select id="account_code" name="account_code" required class="input mt-1.5 {{ $errors->has('account_code') ? 'border-rose-400' : '' }}">
                        <option value="">Select account code…</option>
                        @foreach ($accountCodes as $a)
                        <option value="{{ $a->code ?? $a->id }}" @if(old('account_code')===($a->code ?? $a->id)) selected @endif>{{ $a->code ?? $a->id }}</option>
                        @endforeach
                    </select>
                    @if ($errors->has('account_code'))<p class="mt-1 text-xs font-medium text-rose-600">{{ $errors->first('account_code') }}</p>@endif
                </div>

                {{-- Description --}}
                <div class="sm:col-span-2 lg:col-span-3">
                    <label for="description" class="block text-sm font-medium text-slate-700">Description <span class="text-rose-500">*</span></label>
                    <textarea id="description" name="description" rows="2" required
                        placeholder="Brief description of what this order is for…"
                        class="input mt-1.5 {{ $errors->has('description') ? 'border-rose-400' : '' }}">{{ old('description') }}</textarea>
                    @if ($errors->has('description'))<p class="mt-1 text-xs font-medium text-rose-600">{{ $errors->first('description') }}</p>@endif
                </div>

                {{-- Notes --}}
                <div class="sm:col-span-2 lg:col-span-3">
                    <label for="notes" class="block text-sm font-medium text-slate-700">Notes</label>
                    <textarea id="notes" name="notes" rows="2"
                        placeholder="Optional internal notes…"
                        class="input mt-1.5 {{ $errors->has('notes') ? 'border-rose-400' : '' }}">{{ old('notes') }}</textarea>
                    @if ($errors->has('notes'))<p class="mt-1 text-xs font-medium text-rose-600">{{ $errors->first('notes') }}</p>@endif
                </div>
            </div>
        </section>

        {{-- ===== Order line items ===== --}}
        <section class="card animate-fade-in mt-6">
            <div class="flex flex-col gap-3 border-b border-slate-200 p-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-base font-semibold text-slate-900">Order Items</h3>
                    <p class="mt-0.5 text-sm text-slate-500">Add one line per item on the quote. Totals are calculated automatically.</p>
                </div>
                <button type="button" id="add-item-btn" class="btn btn-secondary py-2">
                    <i data-lucide="plus" class="h-4 w-4"></i>
                    Add Item
                </button>
            </div>

            {{-- Desktop table --}}
            <div class="table-wrap hidden md:block">
                <table class="data-table" id="items-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Item ID</th>
                            <th>UOM</th>
                            <th class="text-right">Quantity</th>
                            <th class="text-right">Unit Price</th>
                            <th class="text-right">Total</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody id="items-tbody">
                        @foreach ($oldItems as $i => $item)
                        @include('ge-orders._line-item-row', ['i' => $i, 'item' => $item, 'items' => $items])
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Mobile cards --}}
            <div id="items-cards" class="divide-y divide-slate-100 md:hidden">
                {{-- Mobile rendering is handled by JS from the same data source --}}
            </div>

            {{-- Order total --}}
            <div class="flex items-center justify-end gap-6 border-t border-slate-200 p-5">
                <div class="text-right">
                    <p class="text-sm font-medium text-slate-500">Order Total</p>
                    <p id="order-total" class="mt-1 text-2xl font-bold tracking-tight text-slate-900">K 0.00</p>
                </div>
            </div>
        </section>

        {{-- ===== Actions ===== --}}
        <div class="mt-6 flex flex-col-reverse items-stretch gap-3 sm:flex-row sm:items-center sm:justify-end">
            <a href="{{ route('ge-orders.index') }}" class="btn btn-secondary">
                <i data-lucide="x" class="h-4 w-4"></i>
                Cancel
            </a>
            <button type="submit" name="action" value="save_draft" formaction="{{ route('ge-orders.store') }}" class="btn btn-secondary">
                <i data-lucide="save" class="h-4 w-4"></i>
                Save as Draft
            </button>
            <button type="submit" name="action" value="submit" formaction="{{ route('ge-orders.store') }}" class="btn btn-primary">
                <i data-lucide="send" class="h-4 w-4"></i>
                Submit for Approval
            </button>
        </div>
    </form>

    @push('scripts')
    <script>
        // GE Order line-item management: add/remove rows + live totals.
        (function() {
            const tbody = document.getElementById('items-tbody');
            const cardsEl = document.getElementById('items-cards');
            const totalEl = document.getElementById('order-total');
            if (!tbody) return;

            // Item options from PHP — used to populate selects and validate STOCK items.
            const inventoryItems = JSON.parse(atob(tbody.closest('form').dataset.inventoryItems));
            const inventoryFlagEl = document.getElementById('inventory_flag');

            const fmtMoney = (n) => 'K ' + (Number(n) || 0).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });

            // Recalculate a single row's total and return it.
            function recalcRow(row) {
                const qty = parseFloat(row.querySelector('[data-field="quantity"]')?.value) || 0;
                const price = parseFloat(row.querySelector('[data-field="unit_price"]')?.value) || 0;
                const total = qty * price;
                const totalCell = row.querySelector('[data-field="total_display"]');
                const totalInput = row.querySelector('[data-field="total"]');
                if (totalCell) totalCell.textContent = fmtMoney(total);
                if (totalInput) totalInput.value = total.toFixed(2);
                return total;
            }

            // Recalculate the overall order total.
            function recalcTotal() {
                let sum = 0;
                tbody.querySelectorAll('tr[data-item-row]').forEach((row) => {
                    sum += recalcRow(row);
                });
                if (totalEl) totalEl.textContent = fmtMoney(sum);
                renderMobile();
            }

            // Build a new row. index is the next free line index.
            function buildRow(index, data) {
                data = data || {};
                const isStock = (inventoryFlagEl?.value || 'STOCK') === 'STOCK';
                const itemOptions = inventoryItems.map((it) =>
                    `<option value="${it.id}" data-uom="${it.uom}" ${String(data.item_id ?? '') === String(it.id) ? 'selected' : ''}>${it.name}</option>`
                ).join('');

                const tr = document.createElement('tr');
                tr.setAttribute('data-item-row', '');
                tr.innerHTML = `
                <td>
                    ${isStock ? `
                        <select name="items[${index}][item_id]" data-field="item_id" class="input py-2 item-select" required>
                            <option value="">Select item…</option>
                            ${itemOptions}
                        </select>
                        <input type="hidden" name="items[${index}][description]" data-field="description-hidden" value="${data.description ?? (data.item_name ?? '')}">
                    ` : `
                        <input type="text" name="items[${index}][description]" data-field="description"
                               value="${data.description ?? ''}" placeholder="Item description"
                               class="input py-2" required>
                    `}
                </td>
                <td>
                    <input type="text" name="items[${index}][item_id_text]" data-field="item_id_text"
                           value="${data.item_id ?? ''}" placeholder="—"
                              class="input py-2 ${isStock ? 'bg-slate-50' : ''}" ${isStock ? 'readonly' : ''}>
                </td>
                <td>
                    <input type="text" name="items[${index}][unit]" data-field="unit"
                           value="${data.unit ?? ''}" placeholder="unit"
                           class="input py-2 w-24">
                </td>
                <td class="text-right">
                    <input type="number" name="items[${index}][quantity]" data-field="quantity"
                           value="${data.quantity ?? ''}" min="0" step="0.01" placeholder="0"
                           class="input py-2 w-24 text-right tabular-nums" required>
                </td>
                <td class="text-right">
                    <input type="number" name="items[${index}][unit_price]" data-field="unit_price"
                           value="${data.unit_price ?? ''}" min="0" step="0.01" placeholder="0.00"
                           class="input py-2 w-28 text-right tabular-nums" required>
                </td>
                <td class="text-right">
                    <span data-field="total_display" class="font-medium tabular-nums text-slate-700">K 0.00</span>
                    <input type="hidden" name="items[${index}][total]" data-field="total" value="0">
                </td>
                <td class="text-right">
                    <button type="button" data-remove-row
                            class="inline-flex items-center justify-center rounded-md p-1.5 text-slate-400 hover:bg-rose-50 hover:text-rose-600">
                        <i data-lucide="trash-2" class="h-4 w-4"></i>
                    </button>
                </td>
            `;
                return tr;
            }

            function nextIndex() {
                const rows = tbody.querySelectorAll('tr[data-item-row]');
                return rows.length ? Array.from(rows).reduce((max, r) => {
                    const m = (r.querySelector('[data-field="quantity"]')?.name || '').match(/items\[(\d+)\]/);
                    return m ? Math.max(max, parseInt(m[1], 10)) : max;
                }, 0) + 1 : 0;
            }

            function addRow(data) {
                const tr = buildRow(nextIndex(), data);
                tbody.appendChild(tr);
                bindRow(tr);
                if (window.lucide?.createIcons) window.lucide.createIcons();
                recalcTotal();
            }

            function bindRow(row) {
                row.querySelectorAll('[data-field="quantity"],[data-field="unit_price"]').forEach((el) => {
                    el.addEventListener('input', recalcTotal);
                });
                row.querySelector('[data-remove-row]')?.addEventListener('click', () => {
                    row.remove();
                    recalcTotal();
                });
                // When STOCK flag is on, selecting an item auto-fills UOM, item id text, and description.
                const sel = row.querySelector('.item-select');
                if (sel) {
                    sel.addEventListener('change', () => {
                        const opt = sel.selectedOptions[0];
                        const uom = row.querySelector('[data-field="unit"]');
                        const idText = row.querySelector('[data-field="item_id_text"]');
                        const hiddenDesc = row.querySelector('[data-field="description-hidden"]');
                        if (uom && opt?.dataset.uom) uom.value = opt.dataset.uom;
                        if (idText) idText.value = sel.value;
                        if (hiddenDesc) hiddenDesc.value = opt?.dataset.description ?? '';
                    });
                }
            }

            // Mobile: render compact cards from the same rows.
            function renderMobile() {
                if (!cardsEl) return;
                cardsEl.innerHTML = '';
                tbody.querySelectorAll('tr[data-item-row]').forEach((row, idx) => {
                    const desc = row.querySelector(isStock() ? '.item-select' : '[data-field="description"]');
                    const qty = row.querySelector('[data-field="quantity"]')?.value || '0';
                    const price = row.querySelector('[data-field="unit_price"]')?.value || '0';
                    const total = row.querySelector('[data-field="total_display"]')?.textContent || 'K 0.00';
                    const card = document.createElement('div');
                    card.className = 'p-4';
                    card.innerHTML = `
                    <div class="flex items-start justify-between gap-2">
                        <p class="text-sm font-semibold text-slate-900">Item ${idx + 1}</p>
                        <button type="button" class="text-slate-400 hover:text-rose-600" onclick="this.closest('div').remove()">
                            <i data-lucide="trash-2" class="h-4 w-4"></i>
                        </button>
                    </div>
                    <div class="mt-2 grid grid-cols-2 gap-2 text-sm">
                        <span class="text-slate-500">Qty</span><span class="text-right font-medium">${qty}</span>
                        <span class="text-slate-500">Unit Price</span><span class="text-right font-medium">${price}</span>
                        <span class="text-slate-500">Total</span><span class="text-right font-semibold tabular-nums">${total}</span>
                    </div>`;
                    cardsEl.appendChild(card);
                });
                if (window.lucide?.createIcons) window.lucide.createIcons();
            }

            function isStock() {
                return (inventoryFlagEl?.value || 'STOCK') === 'STOCK';
            }

            // Add Item button.
            document.getElementById('add-item-btn')?.addEventListener('click', () => addRow());

            // When inventory flag changes, rebuild all rows (STOCK toggles description vs item select).
            inventoryFlagEl?.addEventListener('change', () => {
                const existing = Array.from(tbody.querySelectorAll('tr[data-item-row]')).map((r) => ({
                    item_id: r.querySelector('[data-field="item_id_text"]')?.value || r.querySelector('[data-field="item_id"]')?.value || '',
                    description: r.querySelector('[data-field="description"]')?.value || r.querySelector('[data-field="description-hidden"]')?.value || '',
                    item_name: r.querySelector('[data-field="item_id"]')?.selectedOptions[0]?.dataset?.description || '',
                    unit: r.querySelector('[data-field="unit"]')?.value || '',
                    quantity: r.querySelector('[data-field="quantity"]')?.value || '',
                    unit_price: r.querySelector('[data-field="unit_price"]')?.value || '',
                }));
                tbody.innerHTML = '';
                if (existing.length === 0) {
                    addRow();
                } else {
                    existing.forEach((d) => addRow(d));
                }
            });

            // Bind any rows already rendered by Blade.
            tbody.querySelectorAll('tr[data-item-row]').forEach(bindRow);
            recalcTotal();
        })();
    </script>
    @endpush

</x-app-layout>