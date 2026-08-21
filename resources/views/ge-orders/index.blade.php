<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">GE Orders</h2>
    </x-slot>

    @php
    /* Variables provided by GEOrderController::index():
    * $orders — Illuminate\Contracts\Pagination\LengthAwarePaginator
    * $filters — array of active filter values (search, status, approval, requester, from, to)
    * $requesters — collection of users for the requester filter (id => name)
    * $statusCounts — optional array of counts per status for the summary cards
    *
    * All variables fall back to demo data so the view renders during development.
    */
    $filters = $filters ?? [];
    $requesters = $requesters ?? collect([
    (object)['id' => 1, 'name' => 'John Smith'],
    (object)['id' => 2, 'name' => 'Mary Doe'],
    (object)['id' => 3, 'name' => 'Alex Wari'],
    (object)['id' => 4, 'name' => 'Sarah Tama'],
    ]);

    $statusCounts = $statusCounts ?? [
    'all' => 128,
    'draft' => 32,
    'pending' => 14,
    'approved' => 76,
    'rejected' => 4,
    'cancelled'=> 2,
    ];

    $search = $filters['search'] ?? old('search', request('search'));
    $status = $filters['status'] ?? old('status', request('status'));
    $approval = $filters['approval'] ?? old('approval', request('approval'));
    $requester = $filters['requester'] ?? old('requester', request('requester'));
    $from = $filters['from'] ?? old('from', request('from'));
    $to = $filters['to'] ?? old('to', request('to'));
    @endphp

    <x-page-header
        title="GE Orders"
        description="Manage General Expenditure orders and approval requests.">
        <x-slot name="actions">
            @can('create', App\Models\GEOrder::class)
            <a href="{{ route('ge-orders.create') }}" class="btn btn-primary">
                <i data-lucide="plus" class="h-4 w-4"></i>
                Create GE Order
            </a>
            @endcan
        </x-slot>
    </x-page-header>

    {{-- Summary stat cards --}}
    <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-5">
        @php
        $summaryCards = [
        ['label' => 'All Orders', 'value' => $statusCounts['all'] ?? 0, 'icon' => 'file-text', 'color' => 'brand', 'filter' => null],
        ['label' => 'Drafts', 'value' => $statusCounts['draft'] ?? 0, 'icon' => 'file-edit', 'color' => 'slate', 'filter' => ['status' => 'draft']],
        ['label' => 'Pending', 'value' => $statusCounts['pending'] ?? 0, 'icon' => 'clock', 'color' => 'amber', 'filter' => ['status' => 'pending']],
        ['label' => 'Approved', 'value' => $statusCounts['approved'] ?? 0, 'icon' => 'check-circle', 'color' => 'emerald', 'filter' => ['status' => 'approved']],
        ['label' => 'Rejected', 'value' => $statusCounts['rejected'] ?? 0, 'icon' => 'x-circle', 'color' => 'rose', 'filter' => ['status' => 'rejected']],
        ];
        $colorMap = [
        'brand' => 'bg-brand-50 text-brand-600',
        'amber' => 'bg-amber-50 text-amber-600',
        'emerald' => 'bg-emerald-50 text-emerald-600',
        'rose' => 'bg-rose-50 text-rose-600',
        'sky' => 'bg-sky-50 text-sky-600',
        'violet' => 'bg-violet-50 text-violet-600',
        'slate' => 'bg-slate-100 text-slate-500',
        ];
        @endphp
        @foreach ($summaryCards as $card)
        @php
        $active = isset($card['filter']) && $card['filter'] !== null
        && ($card['filter']['status'] ?? null) === $status;
        $href = $card['filter'] ? route('ge-orders.index', $card['filter']) : route('ge-orders.index');
        @endphp
        <a href="{{ $href }}"
            class="card card-hover animate-fade-in flex items-center gap-4 p-4 {{ $active ? 'ring-2 ring-brand-500/40' : '' }}">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg {{ $colorMap[$card['color']] }}">
                <i data-lucide="{{ $card['icon'] }}" class="h-5 w-5"></i>
            </span>
            <div class="min-w-0">
                <p class="text-xs font-medium text-slate-500">{{ $card['label'] }}</p>
                <p class="text-xl font-bold tracking-tight text-slate-900">{{ $card['value'] }}</p>
            </div>
        </a>
        @endforeach
    </div>

    {{-- Filters + table --}}
    <section class="card animate-fade-in">

        {{-- Filter bar --}}
        <form method="GET" action="{{ route('ge-orders.index') }}" class="border-b border-slate-200 p-5">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
                <div class="relative xl:col-span-2">
                    <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"></i>
                    <input type="search" name="search" value="{{ $search }}"
                        placeholder="Search order #, description, supplier…"
                        class="input pl-9">
                </div>
                <select name="status" class="input">
                    <option value="">All statuses</option>
                    @foreach (['draft' => 'Draft', 'pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'cancelled' => 'Cancelled'] as $val => $lbl)
                    <option value="{{ $val }}" @if((string)$status===$val) selected @endif>{{ $lbl }}</option>
                    @endforeach
                </select>
                <select name="approval" class="input">
                    <option value="">All approvals</option>
                    @foreach (['not submitted' => 'Not Submitted', 'pending approval' => 'Pending Approval', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $val => $lbl)
                    <option value="{{ $val }}" @if((string)$approval===$val) selected @endif>{{ $lbl }}</option>
                    @endforeach
                </select>
                <select name="requester" class="input">
                    <option value="">All requesters</option>
                    @foreach ($requesters as $r)
                    <option value="{{ $r->id }}" @if((string)$requester===(string)$r->id) selected @endif>{{ $r->name }}</option>
                    @endforeach
                </select>
                <div class="flex gap-2">
                    <input type="date" name="from" value="{{ $from }}" class="input" placeholder="From" title="From date">
                    <input type="date" name="to" value="{{ $to }}" class="input" placeholder="To" title="To date">
                </div>
            </div>
            <div class="mt-3 flex items-center justify-between">
                <p class="text-xs text-slate-400">
                    @if ($orders->total() ?? 0)
                    Showing {{ $orders->firstItem() ?? 1 }}–{{ $orders->lastItem() ?? $orders->count() }} of {{ $orders->total() ?? $orders->count() }}
                    @endif
                </p>
                <div class="flex items-center gap-2">
                    <a href="{{ route('ge-orders.index') }}" class="btn btn-ghost py-2">
                        <i data-lucide="rotate-ccw" class="h-4 w-4"></i>
                        Clear filters
                    </a>
                    <button type="submit" class="btn btn-primary py-2">
                        <i data-lucide="filter" class="h-4 w-4"></i>
                        Apply
                    </button>
                </div>
            </div>
        </form>

        {{-- Table or empty state --}}
        @if (($orders->count() ?? 0) > 0)
        <div class="table-wrap">
            <table class="data-table hidden md:table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Requester</th>
                        <th>Date</th>
                        <th>Description</th>
                        <th class="text-right">Amount</th>
                        <th>Status</th>
                        <th>Approval</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $order)
                    @php
                    $status = strtolower($order->status ?? 'draft');
                    $approval = strtolower($order->approval_status ?? $order->approval ?? 'not submitted');
                    $canEdit = auth()->user()?->can('update', $order) ?? false;
                    $canApprove = auth()->user()?->can('approve', $order) ?? false;
                    $canDelete = auth()->user()?->can('delete', $order) ?? false;
                    $canSubmit = in_array($status, ['draft']) && (auth()->user()?->can('submit', $order) ?? false);
                    @endphp
                    <tr>
                        <td>
                            <a href="{{ route('ge-orders.show', $order->id) }}"
                                class="font-mono text-xs font-semibold text-brand-600 hover:text-brand-700">
                                {{ $order->order_number ?? ('GE-'.str_pad($order->id, 5, '0', STR_PAD_LEFT)) }}
                            </a>
                        </td>
                        <td>{{ $order->requester?->name ?? $order->user?->name ?? '—' }}</td>
                        <td class="text-slate-500">{{ optional($order->order_date ?? $order->created_at)->format('d M Y') }}</td>
                        <td class="max-w-[16rem] truncate text-slate-600">{{ $order->description ?? '—' }}</td>
                        <td class="text-right font-medium tabular-nums">{{ is_object($order->total_amount) ? 'K '.$order->total_amount->format(0) : 'K '.number_format((float)($order->total_amount ?? 0)) }}</td>
                        <td><x-status-badge :status="$order->status" /></td>
                        <td><x-status-badge :status="$order->approval_status ?? $order->approval ?? 'not submitted'" /></td>
                        <td class="text-right">
                            <div class="inline-flex items-center gap-1">
                                <a href="{{ route('ge-orders.show', $order->id) }}"
                                    class="inline-flex items-center justify-center rounded-md p-1.5 text-slate-500 hover:bg-slate-100 hover:text-brand-600"
                                    title="View">
                                    <i data-lucide="eye" class="h-4 w-4"></i>
                                </a>
                                @if ($canEdit)
                                <a href="{{ route('ge-orders.edit', $order->id) }}"
                                    class="inline-flex items-center justify-center rounded-md p-1.5 text-slate-500 hover:bg-slate-100 hover:text-brand-600"
                                    title="Edit">
                                    <i data-lucide="pencil" class="h-4 w-4"></i>
                                </a>
                                @endif
                                @if ($canSubmit)
                                <form method="POST" action="{{ route('ge-orders.submit', $order->id) }}" class="inline">
                                    @csrf
                                    <button type="submit"
                                        class="inline-flex items-center justify-center rounded-md p-1.5 text-slate-500 hover:bg-amber-50 hover:text-amber-600"
                                        title="Submit for Approval">
                                        <i data-lucide="send" class="h-4 w-4"></i>
                                    </button>
                                </form>
                                @endif
                                @if ($canApprove && $approval === 'pending approval')
                                <a href="{{ route('ge-orders.show', $order->id) }}#approval"
                                    class="inline-flex items-center justify-center rounded-md p-1.5 text-slate-500 hover:bg-emerald-50 hover:text-emerald-600"
                                    title="Approve / Reject">
                                    <i data-lucide="check" class="h-4 w-4"></i>
                                </a>
                                @endif
                                @if ($canDelete)
                                <button type="button"
                                    data-confirm
                                    data-confirm-title="Delete GE Order?"
                                    data-confirm-message="This action cannot be undone. The order and all its line items will be permanently removed."
                                    data-confirm-action="{{ route('ge-orders.destroy', $order->id) }}"
                                    data-confirm-method="DELETE"
                                    class="inline-flex items-center justify-center rounded-md p-1.5 text-slate-500 hover:bg-rose-50 hover:text-rose-600"
                                    title="Delete">
                                    <i data-lucide="trash-2" class="h-4 w-4"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Mobile card layout --}}
            <div class="divide-y divide-slate-100 md:hidden">
                @foreach ($orders as $order)
                @php
                $status = strtolower($order->status ?? 'draft');
                $approval = strtolower($order->approval_status ?? $order->approval ?? 'not submitted');
                @endphp
                <div class="p-4">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <a href="{{ route('ge-orders.show', $order->id) }}"
                                class="font-mono text-sm font-semibold text-brand-600">
                                {{ $order->order_number ?? ('GE-'.str_pad($order->id, 5, '0', STR_PAD_LEFT)) }}
                            </a>
                            <p class="mt-0.5 truncate text-xs text-slate-500">{{ $order->description ?? '—' }}</p>
                        </div>
                        <x-status-badge :status="$order->status" />
                    </div>
                    <div class="mt-3 flex items-center justify-between text-sm">
                        <span class="text-slate-500">{{ $order->requester?->name ?? $order->user?->name ?? '—' }}</span>
                        <span class="font-semibold tabular-nums text-slate-900">{{ is_object($order->total_amount) ? 'K '.$order->total_amount->format(0) : 'K '.number_format((float)($order->total_amount ?? 0)) }}</span>
                    </div>
                    <div class="mt-2 flex items-center justify-between">
                        <x-status-badge :status="$order->approval_status ?? $order->approval ?? 'not submitted'" />
                        <a href="{{ route('ge-orders.show', $order->id) }}" class="text-sm font-medium text-brand-600">View</a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Pagination --}}
        @if (method_exists($orders, 'links'))
        <div class="border-t border-slate-200 px-5 py-3">
            {{ $orders->appends(request()->query())->links('pagination::tailwind') }}
        </div>
        @endif
        @else
        <x-empty-state
            icon="file-text"
            title="No GE Orders found"
            message="No orders match your current filters, or no General Expenditure orders have been created yet.">
            <x-slot name="actions">
                @can('create', App\Models\GEOrder::class)
                <a href="{{ route('ge-orders.create') }}" class="btn btn-primary">
                    <i data-lucide="plus" class="h-4 w-4"></i>
                    Create GE Order
                </a>
                @endcan
                <a href="{{ route('ge-orders.index') }}" class="btn btn-secondary">Clear filters</a>
            </x-slot>
        </x-empty-state>
        @endif
    </section>

    {{-- Delete confirmation modal --}}
    <div id="confirm-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" data-confirm-close></div>
        <div class="relative w-full max-w-md rounded-xl bg-white p-6 shadow-xl animate-fade-in">
            <div class="flex items-start gap-4">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-rose-50 text-rose-600">
                    <i data-lucide="alert-triangle" class="h-5 w-5"></i>
                </span>
                <div class="min-w-0">
                    <h3 id="confirm-title" class="text-base font-semibold text-slate-900">Are you sure?</h3>
                    <p id="confirm-message" class="mt-1 text-sm text-slate-500">This action cannot be undone.</p>
                </div>
            </div>
            <form id="confirm-form" method="POST" action="" class="mt-5 flex items-center justify-end gap-3">
                @csrf
                <input type="hidden" name="_method" id="confirm-method-input" value="POST">
                <button type="button" class="btn btn-secondary" data-confirm-close>Cancel</button>
                <button type="submit" class="btn bg-rose-600 text-white hover:bg-rose-700 focus:ring-rose-500">
                    <i data-lucide="trash-2" class="h-4 w-4"></i>
                    Delete
                </button>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        // Generic confirmation modal for destructive actions.
        (function() {
            const modal = document.getElementById('confirm-modal');
            if (!modal) return;
            const titleEl = document.getElementById('confirm-title');
            const msgEl = document.getElementById('confirm-message');
            const formEl = document.getElementById('confirm-form');
            const methodEl = document.getElementById('confirm-method-input');

            const open = (btn) => {
                titleEl.textContent = btn.dataset.confirmTitle || 'Are you sure?';
                msgEl.textContent = btn.dataset.confirmMessage || 'This action cannot be undone.';
                formEl.action = btn.dataset.confirmAction;
                methodEl.value = btn.dataset.confirmMethod || 'POST';
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            };
            const close = () => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            };

            document.querySelectorAll('[data-confirm]').forEach((btn) => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    open(btn);
                });
            });
            document.querySelectorAll('[data-confirm-close]').forEach((el) => {
                el.addEventListener('click', close);
            });
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') close();
            });
        })();
    </script>
    @endpush

</x-app-layout>