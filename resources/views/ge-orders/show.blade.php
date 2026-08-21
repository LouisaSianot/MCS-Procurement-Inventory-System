<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">GE Order {{ $order->order_number ?? '' }}</h2>
    </x-slot>

    @php
    /* Variables from GEOrderController::show():
    * $order — GEOrder model with relations: items, requester (user), supplier, branch, approver
    * $canApprove — bool, whether the current user may approve/reject
    */
    $status = strtolower($order->status ?? 'draft');
    $approval = strtolower($order->approval_status ?? $order->approval ?? 'not submitted');
    $isRejected = in_array($approval, ['rejected']) || $status === 'rejected';
    $isCancelled = $status === 'cancelled';
    $isEditable = in_array($status, ['draft']) && (auth()->user()?->can('update', $order) ?? false);
    $canSubmit = in_array($status, ['draft']) && (auth()->user()?->can('submit', $order) ?? false);
    $canApprove = $canApprove ?? (auth()->user()?->can('approve', $order) ?? false);
    $canCancel = ! in_array($status, ['cancelled','completed']) && (auth()->user()?->can('cancel', $order) ?? false);
    @endphp

    <x-page-header
        title="GE Order {{ $order->order_number ?? '' }}"
        description="Order details, approval status, and line items."
        :breadcrumbs="[
        ['label' => 'GE Orders', 'url' => route('ge-orders.index')],
        ['label' => $order->order_number ?? ''],
    ]">
        <x-slot name="actions">
            <a href="{{ route('ge-orders.index') }}" class="btn btn-secondary">
                <i data-lucide="arrow-left" class="h-4 w-4"></i>
                Back to GE Orders
            </a>
            @if ($isEditable)
            <a href="{{ route('ge-orders.edit', $order->id) }}" class="btn btn-primary">
                <i data-lucide="pencil" class="h-4 w-4"></i>
                Edit
            </a>
            @endif
        </x-slot>
    </x-page-header>

    {{-- Status + approval summary banner --}}
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-wrap items-center gap-2">
            <x-status-badge :status="$order->status" />
            <x-status-badge :status="$order->approval_status ?? $order->approval ?? 'not submitted'" />
            @if ($isCancelled)
            <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500 ring-1 ring-inset ring-slate-200">
                <i data-lucide="ban" class="h-3 w-3"></i> Cancelled
            </span>
            @endif
        </div>
        <p class="text-sm text-slate-500">
            Created {{ optional($order->created_at)->format('d M Y, H:i') ?? '—' }}
        </p>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        {{-- Left: order details + items --}}
        <div class="space-y-6 lg:col-span-2">

            {{-- Order details card --}}
            <section class="card animate-fade-in">
                <div class="border-b border-slate-200 p-5">
                    <h3 class="text-base font-semibold text-slate-900">Order Information</h3>
                </div>
                <dl class="grid grid-cols-1 gap-px bg-slate-100 sm:grid-cols-2">
                    @php
                    $detailRows = [
                    ['label' => 'GE Number', 'value' => $order->order_number ?? '—'],
                    ['label' => 'Requester', 'value' => $order->requester?->name ?? $order->user?->name ?? '—'],
                    ['label' => 'Department', 'value' => $order->branch?->name ?? ('Branch '.$order->branch_id ?? '—')],
                    ['label' => 'Order Date', 'value' => optional($order->order_date ?? $order->created_at)->format('d M Y') ?? '—'],
                    ['label' => 'Supplier', 'value' => $order->supplier?->name ?? '—'],
                    ['label' => 'Account Code', 'value' => $order->account_code ?? '—'],
                    ['label' => 'Inventory Flag', 'value' => strtoupper($order->inventory_flag ?? 'NONSTOCK')],
                    ['label' => 'PO Number', 'value' => $order->po_number ?? '—'],
                    ];
                    @endphp
                    @foreach ($detailRows as $row)
                    <div class="bg-white px-5 py-4">
                        <dt class="text-xs font-medium uppercase tracking-wider text-slate-400">{{ $row['label'] }}</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-900">{{ $row['value'] }}</dd>
                    </div>
                    @endforeach
                    <div class="bg-white px-5 py-4 sm:col-span-2">
                        <dt class="text-xs font-medium uppercase tracking-wider text-slate-400">Description</dt>
                        <dd class="mt-1 text-sm text-slate-700">{{ $order->description ?? '—' }}</dd>
                    </div>
                    @if (! empty($order->notes))
                    <div class="bg-white px-5 py-4 sm:col-span-2">
                        <dt class="text-xs font-medium uppercase tracking-wider text-slate-400">Notes</dt>
                        <dd class="mt-1 text-sm text-slate-700">{{ $order->notes }}</dd>
                    </div>
                    @endif
                </dl>
            </section>

            {{-- Order items --}}
            <section class="card animate-fade-in">
                <div class="flex items-center justify-between border-b border-slate-200 p-5">
                    <h3 class="text-base font-semibold text-slate-900">Order Items</h3>
                    <span class="text-sm text-slate-500">{{ $order->items?->count() ?? 0 }} item(s)</span>
                </div>
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Item ID</th>
                                <th>UOM</th>
                                <th class="text-right">Quantity</th>
                                <th class="text-right">Unit Price</th>
                                <th class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach (($order->items ?? collect()) as $item)
                            <tr>
                                <td class="font-medium text-slate-900">{{ $item->description ?? $item->item?->name ?? '—' }}</td>
                                <td class="font-mono text-xs text-slate-500">{{ $item->item_id ?? '—' }}</td>
                                <td class="text-slate-500">{{ $item->unit ?? '—' }}</td>
                                <td class="text-right tabular-nums">{{ number_format((float)$item->quantity, 2) }}</td>
                                <td class="text-right tabular-nums">{{ is_object($item->unit_price) ? 'K '.$item->unit_price->format(2) : 'K '.number_format((float)$item->unit_price, 2) }}</td>
                                <td class="text-right font-medium tabular-nums">{{ is_object($item->total) ? 'K '.$item->total->format(2) : 'K '.number_format((float)$item->total, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" class="px-4 py-3 text-right text-sm font-semibold text-slate-700">Order Total</td>
                                <td class="px-4 py-3 text-right text-base font-bold tabular-nums text-slate-900">
                                    {{ is_object($order->total_amount) ? 'K '.$order->total_amount->format(2) : 'K '.number_format((float)($order->total_amount ?? 0), 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </section>

            {{-- Rejection reason --}}
            @if ($isRejected && ! empty($order->rejection_reason))
            <section class="rounded-xl border border-rose-200 bg-rose-50 p-5 animate-fade-in">
                <div class="flex items-start gap-3">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-rose-100 text-rose-600">
                        <i data-lucide="x-circle" class="h-5 w-5"></i>
                    </span>
                    <div class="min-w-0">
                        <h3 class="text-sm font-semibold text-rose-900">Rejection Reason</h3>
                        <p class="mt-1 text-sm text-rose-700">{{ $order->rejection_reason }}</p>
                        @if (! empty($order->approver))
                        <p class="mt-2 text-xs text-rose-500">By {{ $order->approver->name ?? '—' }} on {{ optional($order->approved_at)->format('d M Y, H:i') ?? '—' }}</p>
                        @endif
                    </div>
                </div>
            </section>
            @endif
        </div>

        {{-- Right: timeline + actions + approval --}}
        <div class="space-y-6 lg:col-span-1">

            {{-- Approval timeline --}}
            <x-approval-timeline :order="$order" />

            {{-- Action buttons --}}
            <section class="card animate-fade-in p-5">
                <h3 class="text-base font-semibold text-slate-900">Actions</h3>
                <div class="mt-4 flex flex-col gap-2.5">
                    @if ($canSubmit)
                    <form method="POST" action="{{ route('ge-orders.submit', $order->id) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary w-full">
                            <i data-lucide="send" class="h-4 w-4"></i>
                            Submit for Approval
                        </button>
                    </form>
                    @endif

                    @if ($isEditable)
                    <a href="{{ route('ge-orders.edit', $order->id) }}" class="btn btn-secondary w-full">
                        <i data-lucide="pencil" class="h-4 w-4"></i>
                        Edit Order
                    </a>
                    @endif

                    @if ($canCancel)
                    <form method="POST" action="{{ route('ge-orders.cancel', $order->id) }}">
                        @csrf
                        <button type="submit"
                            class="btn btn-secondary w-full hover:bg-slate-100 hover:text-slate-900"
                            onclick="return confirm('Cancel this GE Order? It will be removed from the approval workflow.')">
                            <i data-lucide="ban" class="h-4 w-4"></i>
                            Cancel Order
                        </button>
                    </form>
                    @endif
                </div>
            </section>

            {{-- Approval interface --}}
            <section id="approval" class="card animate-fade-in p-5">
                <h3 class="text-base font-semibold text-slate-900">Approval</h3>

                @if ($canApprove && $approval === 'pending approval')
                <p class="mt-1 text-sm text-slate-500">As an authorised approver you can approve or reject this order.</p>

                {{-- Approve --}}
                <form method="POST" action="{{ route('ge-orders.approve', $order->id) }}" class="mt-4">
                    @csrf
                    <button type="submit" class="btn w-full bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500">
                        <i data-lucide="check" class="h-4 w-4"></i>
                        Approve Order
                    </button>
                </form>

                {{-- Reject (with reason) --}}
                <form method="POST" action="{{ route('ge-orders.reject', $order->id) }}" class="mt-3">
                    @csrf
                    <label for="rejection_reason" class="block text-sm font-medium text-slate-700">Rejection reason</label>
                    <textarea id="rejection_reason" name="rejection_reason" rows="3" required
                        placeholder="Explain why this order is being rejected…"
                        class="input mt-1.5 {{ $errors->has('rejection_reason') ? 'border-rose-400' : '' }}"></textarea>
                    @if ($errors->has('rejection_reason'))
                    <p class="mt-1 text-xs font-medium text-rose-600">{{ $errors->first('rejection_reason') }}</p>
                    @endif
                    <button type="submit" class="btn mt-3 w-full bg-rose-600 text-white hover:bg-rose-700 focus:ring-rose-500">
                        <i data-lucide="x" class="h-4 w-4"></i>
                        Reject Order
                    </button>
                </form>
                @elseif ($canApprove && $approval !== 'pending approval')
                <p class="mt-1 text-sm text-slate-500">
                    This order is not awaiting approval (current state: {{ $order->approval_status ?? $order->approval ?? '—' }}).
                </p>
                @else
                <div class="mt-3 flex items-start gap-3 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                    <i data-lucide="lock" class="h-5 w-5 shrink-0 text-slate-400"></i>
                    <span>Approval and rejection are restricted to authorised users. Only orders pending approval can be acted upon.</span>
                </div>
                @endif
            </section>
        </div>
    </div>

</x-app-layout>