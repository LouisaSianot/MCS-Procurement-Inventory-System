<x-app-layout title="Dashboard">

    @php
    /* ---------------------------------------------------------------
    | Dashboard — all data comes from the controller.
    | Empty defaults keep the view safe when a data source is unavailable.
    |--------------------------------------------------------------- */

    // Summary statistics supplied by the dashboard route.
    $totalOrders = $totalOrders ?? 0;
    $pendingOrders = $pendingOrders ?? 0;
    $approvedOrders = $approvedOrders ?? 0;
    $awaitingReceipt = $awaitingReceipt ?? 0;
    $lowStockItems = $lowStockItems ?? 0;
    $totalInventoryItems = $totalInventoryItems ?? 0;
    $totalAssets = $totalAssets ?? 0;

    // Optional trend data is only shown when supplied.
    $ordersChange = $ordersChange ?? null;
    $ordersChangeDir = $ordersChangeDir ?? 'up';
    $lowStockChange = $lowStockChange ?? null;
    $lowStockChangeDir = $lowStockChangeDir ?? 'up';

    // --- Inventory health summary ---
    $stockNormal = $stockNormal ?? 0;
    $stockLow = $stockLow ?? 0;
    $stockOut = $stockOut ?? 0;
    $stockTotal = $stockTotal ?? ($stockNormal + $stockLow + $stockOut);

    // Collections from the dashboard route; empty by default.

    // Recent GE Orders (includes STOCK / NON-STOCK distinction)
    $recentOrders = $recentOrders ?? collect();

    // Low stock items table
    $lowStockTable = $lowStockTable ?? collect();

    // Inventory movements (recent activity)
    $inventoryMovements = $inventoryMovements ?? collect();

    // Inventory alerts
    $inventoryAlerts = $inventoryAlerts ?? collect();

    // Recent activity feed
    $recentActivity = $recentActivity ?? collect();

    // Helper: safe route check — returns the URL if the route exists, '#' otherwise.
    // This prevents Blade errors when modules haven't been wired into routes yet.
    $safeRoute = function ($name, $params = []) {
    return \Illuminate\Support\Facades\Route::has($name) ? route($name, $params) : '#';
    };
    @endphp

    {{-- ============================================================= --}}
    {{-- Page heading                                                 --}}
    {{-- ============================================================= --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold tracking-tight text-slate-900">Dashboard</h2>
            <p class="mt-1 text-sm text-slate-500">
                Welcome back, {{ Auth::user()->name ?? 'Guest' }}
                @if(Auth::user() && isset(Auth::user()->role))
                · <span class="font-medium text-slate-600">{{ Auth::user()->role }}</span>
                @endif
            </p>
        </div>
        @can('ge-orders.create')
        <a href="{{ $safeRoute('ge-orders.create') }}" class="btn btn-primary">
            <i data-lucide="plus" class="h-4 w-4"></i>
            Create GE Order
        </a>
        @endcan
    </div>

    {{-- ============================================================= --}}
    {{-- Decision-first operational queue --}}
    @php
    $attentionItems = collect([
    $stockOut > 0 ? ['count' => $stockOut, 'label' => $stockOut === 1 ? 'item is out of stock' : 'items are out of stock', 'detail' => 'Review affected inventory and replenish critical items.', 'icon' => 'alert-triangle', 'tone' => 'rose', 'action' => 'Review stockouts', 'href' => $safeRoute('inventory.index', ['status' => 'out_of_stock'])] : null,
    $awaitingReceipt > 0 ? ['count' => $awaitingReceipt, 'label' => $awaitingReceipt === 1 ? 'purchase order awaits receipt' : 'purchase orders await receipt', 'detail' => 'Record delivered goods to keep inventory current.', 'icon' => 'package-check', 'tone' => 'sky', 'action' => 'Receive goods', 'href' => $safeRoute('receiving.index')] : null,
    $pendingOrders > 0 ? ['count' => $pendingOrders, 'label' => $pendingOrders === 1 ? 'GE order needs review' : 'GE orders need review', 'detail' => 'Progress pending requests through the procurement queue.', 'icon' => 'file-clock', 'tone' => 'amber', 'action' => 'Review orders', 'href' => $safeRoute('ge-orders.index')] : null,
    $stockLow > 0 ? ['count' => $stockLow, 'label' => $stockLow === 1 ? 'item is below reorder level' : 'items are below reorder level', 'detail' => 'Plan replenishment before stock reaches zero.', 'icon' => 'triangle-alert', 'tone' => 'amber', 'action' => 'View low stock', 'href' => $safeRoute('inventory.index', ['status' => 'low_stock'])] : null,
    ])->filter();
    $attentionTone = ['rose' => 'bg-rose-50 text-rose-600 ring-rose-200', 'amber' => 'bg-amber-50 text-amber-600 ring-amber-200', 'sky' => 'bg-sky-50 text-sky-600 ring-sky-200'];
    @endphp
    <section class="mb-6 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-card" aria-labelledby="attention-heading">
        <div class="flex flex-col gap-1 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 id="attention-heading" class="text-base font-semibold text-slate-900">Needs attention</h3>
                <p class="text-sm text-slate-500">The items most likely to block procurement or operations.</p>
            </div>
            @if ($attentionItems->isNotEmpty())<span class="w-fit rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">{{ $attentionItems->count() }} priorities</span>@endif
        </div>
        @if ($attentionItems->isEmpty())
        <div class="flex items-center gap-3 px-5 py-5 text-sm text-slate-600"><span class="flex h-9 w-9 items-center justify-center rounded-full bg-emerald-50 text-emerald-600"><i data-lucide="circle-check" class="h-5 w-5"></i></span><span><span class="font-semibold text-slate-900">All caught up.</span> There are no urgent procurement or inventory issues right now.</span></div>
        @else
        <div class="grid divide-y divide-slate-100 md:grid-cols-2 md:divide-x md:divide-y-0 xl:grid-cols-4">
            @foreach ($attentionItems as $attention)
            <a href="{{ $attention['href'] }}" class="dashboard-action flex min-w-0 gap-3 p-4"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg ring-1 ring-inset {{ $attentionTone[$attention['tone']] }}"><i data-lucide="{{ $attention['icon'] }}" class="h-5 w-5"></i></span><span class="min-w-0"><span class="block text-sm font-semibold text-slate-900"><span class="text-slate-900">{{ $attention['count'] }}</span> {{ $attention['label'] }}</span><span class="mt-0.5 block text-xs leading-snug text-slate-500">{{ $attention['detail'] }}</span><span class="mt-2 inline-flex items-center gap-1 text-xs font-semibold text-brand-600">{{ $attention['action'] }} <i data-lucide="arrow-right" class="h-3.5 w-3.5"></i></span></span></a>
            @endforeach
        </div>
        @endif
    </section>

    {{-- Summary statistics cards (v4)                                --}}
    {{-- ============================================================= --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-6">
        <x-stat-card title="Total GE Orders" :value="$totalOrders" icon="file-text" icon-color="brand" />
        <x-stat-card title="Pending GE Orders" :value="$pendingOrders" icon="file-clock" icon-color="amber" />
        <x-stat-card title="GE Orders — Approved Status" :value="$approvedOrders" icon="file-check-2" icon-color="emerald" />
        <x-stat-card title="Awaiting Receipt" :value="$awaitingReceipt" icon="truck" icon-color="sky" />
        <x-stat-card title="Low Stock Items" :value="$lowStockItems" icon="alert-triangle" icon-color="rose" />
        <x-stat-card title="Inventory Items" :value="$totalInventoryItems" icon="boxes" icon-color="brand" />
    </div>

    {{-- ============================================================= --}}
    {{-- GE Order Overview + Inventory Health (two columns)          --}}
    {{-- ============================================================= --}}
    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-2">

        {{-- GE Order Overview --}}
        <section class="card">
            <div class="flex items-center justify-between border-b border-slate-200 p-5">
                <div>
                    <h3 class="text-base font-semibold text-slate-900">GE Order Overview</h3>
                    <p class="mt-0.5 text-sm text-slate-500">Recent purchase requests</p>
                </div>
                <a href="{{ $safeRoute('ge-orders.index') }}" class="text-sm font-medium text-brand-600 hover:text-brand-700">View all</a>
            </div>
            <div class="table-wrap">
                <table class="data-table hidden md:table">
                    <thead>
                        <tr>
                            <th>GE Number</th>
                            <th>Supplier</th>
                            <th>Type</th>
                            <th>Date</th>
                            <th class="text-right">Total Cost</th>
                            <th>Status</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentOrders as $order)
                        <tr>
                            <td class="font-mono text-xs font-semibold text-slate-900">{{ $order->number }}</td>
                            <td>{{ $order->supplier }}</td>
                            <td>
                                @if(strtoupper($order->type) === 'STOCK')
                                <span class="inline-flex items-center gap-1 rounded-full bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-700 ring-1 ring-inset ring-brand-200">
                                    <i data-lucide="package" class="h-3 w-3"></i> STOCK
                                </span>
                                @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600 ring-1 ring-inset ring-slate-200">
                                    <i data-lucide="file" class="h-3 w-3"></i> NON-STOCK
                                </span>
                                @endif
                            </td>
                            <td class="text-slate-500">{{ $order->date }}</td>
                            <td class="text-right font-medium tabular-nums">{{ $order->amount }}</td>
                            <td><x-status-badge :status="$order->status" /></td>
                            <td class="text-right">
                                <a href="{{ $safeRoute('ge-orders.show', $order->id ?? 1) }}" class="inline-flex items-center gap-1 text-sm font-medium text-brand-600 hover:text-brand-700">
                                    View <i data-lucide="arrow-right" class="h-3.5 w-3.5"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @if ($recentOrders->isEmpty())
                <p class="px-5 py-4 text-sm text-slate-500">No GE orders have been created yet.</p>
                @endif

                {{-- Mobile card layout --}}
                <div class="divide-y divide-slate-100 md:hidden">
                    @foreach ($recentOrders as $order)
                    <div class="p-4">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <p class="font-mono text-xs font-semibold text-slate-900">{{ $order->number }}</p>
                                <p class="text-xs text-slate-500">{{ $order->supplier }} · {{ $order->date }}</p>
                            </div>
                            <x-status-badge :status="$order->status" />
                        </div>
                        <div class="mt-2 flex items-center justify-between text-sm">
                            <span class="text-slate-600">{{ $order->amount }}</span>
                            <span class="text-xs font-semibold {{ strtoupper($order->type) === 'STOCK' ? 'text-brand-700' : 'text-slate-600' }}">{{ strtoupper($order->type) }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- Inventory Health --}}
        <section class="card">
            <div class="flex items-center justify-between border-b border-slate-200 p-5">
                <div>
                    <h3 class="text-base font-semibold text-slate-900">Inventory Health</h3>
                    <p class="mt-0.5 text-sm text-slate-500">Stock status overview</p>
                </div>
                <a href="{{ $safeRoute('inventory.index') }}" class="text-sm font-medium text-brand-600 hover:text-brand-700">View all</a>
            </div>
            <div class="p-5">

                {{-- Stock status bars --}}
                @php
                $pctNormal = $stockTotal > 0 ? round(($stockNormal / $stockTotal) * 100) : 0;
                $pctLow = $stockTotal > 0 ? round(($stockLow / $stockTotal) * 100) : 0;
                $pctOut = $stockTotal > 0 ? round(($stockOut / $stockTotal) * 100) : 0;
                @endphp
                <div class="space-y-3">
                    <div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="font-medium text-slate-700">Normal Stock</span>
                            <span class="tabular-nums text-slate-500">{{ $stockNormal }}</span>
                        </div>
                        <div class="mt-1.5 h-2.5 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full bg-emerald-500" style="width: {{ $pctNormal }}%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="font-medium text-slate-700">Low Stock</span>
                            <span class="tabular-nums text-slate-500">{{ $stockLow }}</span>
                        </div>
                        <div class="mt-1.5 h-2.5 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full bg-amber-500" style="width: {{ $pctLow }}%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="font-medium text-slate-700">Out of Stock</span>
                            <span class="tabular-nums text-slate-500">{{ $stockOut }}</span>
                        </div>
                        <div class="mt-1.5 h-2.5 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full bg-rose-500" style="width: {{ $pctOut }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Low stock items table --}}
            <div class="border-t border-slate-200 p-5">
                <h4 class="mb-3 text-sm font-semibold text-slate-900">Low Stock Items</h4>
                <div class="table-wrap">
                    <table class="data-table hidden sm:table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th class="text-right">Current Stock</th>
                                <th class="text-right">Reorder Level</th>
                                <th>Location</th>
                                <th class="text-right"><span class="sr-only">Action</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($lowStockTable as $item)
                            <tr>
                                <td class="font-medium text-slate-900">{{ $item->name }}</td>
                                <td class="text-right tabular-nums text-rose-600 font-semibold">{{ $item->current_stock }}</td>
                                <td class="text-right tabular-nums text-slate-500">{{ $item->reorder_level }}</td>
                                <td class="text-slate-500">{{ $item->location }}</td>
                                <td class="text-right"><a href="{{ $safeRoute('inventory.show', ['itemBranch' => $item->id]) }}" class="text-sm font-semibold text-brand-600 hover:text-brand-700">Review<span class="sr-only"> {{ $item->name }}</span></a></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{-- Mobile cards --}}
                    <div class="space-y-2 sm:hidden">
                        @foreach ($lowStockTable as $item)
                        <div class="rounded-lg border border-slate-200 p-3">
                            <div class="flex items-center justify-between">
                                <p class="font-medium text-slate-900">{{ $item->name }}</p>
                                <span class="text-xs text-slate-500">{{ $item->location }}</span>
                            </div>
                            <div class="mt-1 flex items-center justify-between gap-3 text-sm">
                                <span><span class="font-semibold text-rose-600">{{ $item->current_stock }}</span><span class="text-slate-400"> / {{ $item->reorder_level }}</span><span class="ml-2 text-xs font-medium text-rose-600">{{ $item->shortfall }} below reorder</span></span>
                                <a href="{{ $safeRoute('inventory.show', ['itemBranch' => $item->id]) }}" class="font-semibold text-brand-600">Review</a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    </div>

    {{-- ============================================================= --}}
    {{-- Procurement Workflow Visualization                            --}}
    {{-- ============================================================= --}}
    <details class="mt-6 card group">
        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 p-5">
            <div>
                <h3 class="text-base font-semibold text-slate-900">Procurement workflow</h3>
                <p class="mt-0.5 text-sm text-slate-500">Reference the GE Order lifecycle when needed.</p>
            </div><i data-lucide="chevron-down" class="h-5 w-5 text-slate-400 transition-transform duration-150 group-open:rotate-180"></i>
        </summary>
        <div class="grid grid-cols-1 gap-6 border-t border-slate-200 p-5 lg:grid-cols-2">

            {{-- STOCK workflow --}}
            <div class="rounded-lg border border-brand-200 bg-brand-50/30 p-4">
                <div class="mb-3 flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-100 px-2.5 py-1 text-xs font-semibold text-brand-700">
                        <i data-lucide="package" class="h-3.5 w-3.5"></i> STOCK
                    </span>
                    <span class="text-xs text-slate-500">Goods posted to inventory</span>
                </div>
                <div class="flex flex-col gap-0">
                    @php $stockSteps = ['GE Request', 'Status Updated', 'Purchase Order','Supplier','Receipt','Inventory Updated']; @endphp
                    @foreach ($stockSteps as $i => $step)
                    <div class="flex items-center gap-3">
                        <div class="flex flex-col items-center">
                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-brand-600 text-xs font-bold text-white">{{ $i + 1 }}</span>
                            @if(!$loop->last)
                            <span class="h-6 w-px bg-brand-200"></span>
                            @endif
                        </div>
                        <span class="text-sm font-medium text-slate-700">{{ $step }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- NON-STOCK workflow --}}
            <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                <div class="mb-3 flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-600">
                        <i data-lucide="file" class="h-3.5 w-3.5"></i> NON-STOCK
                    </span>
                    <span class="text-xs text-slate-500">Goods not posted to inventory</span>
                </div>
                <div class="flex flex-col gap-0">
                    @php $nonStockSteps = ['GE Request', 'Status Updated', 'Purchase Order','Supplier','Receipt','Complete']; @endphp
                    @foreach ($nonStockSteps as $i => $step)
                    <div class="flex items-center gap-3">
                        <div class="flex flex-col items-center">
                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-slate-500 text-xs font-bold text-white">{{ $i + 1 }}</span>
                            @if(!$loop->last)
                            <span class="h-6 w-px bg-slate-300"></span>
                            @endif
                        </div>
                        <span class="text-sm font-medium text-slate-700">{{ $step }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </details>

    {{-- ============================================================= --}}
    {{-- Inventory Movements + Quick Actions + Alerts (three columns) --}}
    {{-- ============================================================= --}}
    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">

        {{-- Inventory Movements --}}
        <section class="card lg:col-span-1">
            <div class="flex items-center justify-between border-b border-slate-200 p-5">
                <h3 class="text-base font-semibold text-slate-900">Inventory Movements</h3>
                <span class="text-xs text-slate-400">Recent</span>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach ($inventoryMovements as $movement)
                @php
                $isIn = $movement->direction === 'in';
                $moveColor = $isIn
                ? ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'icon' => 'arrow-up-right']
                : ['bg' => 'bg-rose-50', 'text' => 'text-rose-600', 'icon' => 'arrow-down-right'];
                @endphp
                <div class="flex items-center gap-3.5 p-4">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg {{ $moveColor['bg'] }} {{ $moveColor['text'] }}">
                        <i data-lucide="{{ $moveColor['icon'] }}" class="h-4 w-4"></i>
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-slate-900">{{ $movement->delta }} {{ $movement->item }}</p>
                        <p class="mt-0.5 text-xs text-slate-500">{{ $movement->type }}</p>
                    </div>
                </div>
                @endforeach
                @if ($inventoryMovements->isEmpty())
                <p class="px-4 py-5 text-sm text-slate-500">No inventory movements have been recorded yet.</p>
                @endif
            </div>
        </section>

        {{-- Quick Actions (v4 expanded) --}}
        <section class="card lg:col-span-1">
            <div class="border-b border-slate-200 p-5">
                <h3 class="text-base font-semibold text-slate-900">Quick Actions</h3>
                <p class="mt-0.5 text-sm text-slate-500">Common workflows</p>
            </div>
            <div class="grid grid-cols-1 gap-3 p-5 sm:grid-cols-2 lg:grid-cols-1">
                @can('ge-orders.create')
                <a href="{{ $safeRoute('ge-orders.create') }}" class="dashboard-action group flex items-center gap-3 rounded-lg border border-slate-200 p-3 transition-[background-color,border-color,color,transform] duration-150 hover:border-brand-300 hover:bg-brand-50/50">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-50 text-brand-600 group-hover:bg-brand-100"><i data-lucide="file-plus-2" class="h-4 w-4"></i></span>
                    <span class="text-sm font-medium text-slate-700 group-hover:text-brand-700">New GE Order</span>
                    <i data-lucide="arrow-right" class="ml-auto h-4 w-4 text-slate-300 group-hover:text-brand-500"></i>
                </a>
                @endcan
                @can('manage-master-data')
                <a href="{{ $safeRoute('admin.suppliers.create') }}" class="dashboard-action group flex items-center gap-3 rounded-lg border border-slate-200 p-3 transition-[background-color,border-color,color,transform] duration-150 hover:border-emerald-300 hover:bg-emerald-50/50">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 group-hover:bg-emerald-100"><i data-lucide="user-plus" class="h-4 w-4"></i></span>
                    <span class="text-sm font-medium text-slate-700 group-hover:text-emerald-700">Add Supplier</span>
                    <i data-lucide="arrow-right" class="ml-auto h-4 w-4 text-slate-300 group-hover:text-emerald-500"></i>
                </a>
                <a href="{{ $safeRoute('admin.items.create') }}" class="dashboard-action group flex items-center gap-3 rounded-lg border border-slate-200 p-3 transition-[background-color,border-color,color,transform] duration-150 hover:border-violet-300 hover:bg-violet-50/50">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-violet-50 text-violet-600 group-hover:bg-violet-100"><i data-lucide="square-plus" class="h-4 w-4"></i></span>
                    <span class="text-sm font-medium text-slate-700 group-hover:text-violet-700">Create Item</span>
                    <i data-lucide="arrow-right" class="ml-auto h-4 w-4 text-slate-300 group-hover:text-violet-500"></i>
                </a>
                @endcan
                @can('create', App\Models\PurchaseReceipt::class)
                <a href="{{ $safeRoute('receiving.create') }}" class="dashboard-action group flex items-center gap-3 rounded-lg border border-slate-200 p-3 transition-[background-color,border-color,color,transform] duration-150 hover:border-sky-300 hover:bg-sky-50/50">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-sky-50 text-sky-600 group-hover:bg-sky-100"><i data-lucide="package-check" class="h-4 w-4"></i></span>
                    <span class="text-sm font-medium text-slate-700 group-hover:text-sky-700">Receive Purchase</span>
                    <i data-lucide="arrow-right" class="ml-auto h-4 w-4 text-slate-300 group-hover:text-sky-500"></i>
                </a>
                @endcan
            </div>
        </section>

        {{-- Inventory Alerts --}}
        <section class="card lg:col-span-1">
            <div class="flex items-center justify-between border-b border-slate-200 p-5">
                <h3 class="text-base font-semibold text-slate-900">Inventory Alerts</h3>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-600">
                    <i data-lucide="bell-ring" class="h-3.5 w-3.5"></i>
                    {{ $inventoryAlerts->count() }} active
                </span>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach ($inventoryAlerts as $alert)
                @php
                $alertColor = $alert->severity === 'error'
                ? ['bg' => 'bg-rose-50', 'text' => 'text-rose-600', 'ring' => 'ring-rose-200']
                : ($alert->severity === 'warning'
                ? ['bg' => 'bg-amber-50', 'text' => 'text-amber-600', 'ring' => 'ring-amber-200']
                : ['bg' => 'bg-sky-50', 'text' => 'text-sky-600', 'ring' => 'ring-sky-200']);
                @endphp
                <div class="flex gap-3.5 p-4">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg {{ $alertColor['bg'] }} {{ $alertColor['text'] }} ring-1 ring-inset {{ $alertColor['ring'] }}">
                        <i data-lucide="{{ $alert->icon }}" class="h-4 w-4"></i>
                    </span>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-slate-900">{{ $alert->type }}</p>
                        <p class="mt-0.5 text-sm leading-snug text-slate-600">
                            <span class="font-medium text-slate-800">{{ $alert->title }}</span>
                            {{ $alert->message }}
                        </p>
                    </div>
                </div>
                @endforeach
            </div>
        </section>
    </div>

    {{-- ============================================================= --}}
    {{-- Module Overview + Reporting (two columns)                    --}}
    {{-- ============================================================= --}}
    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">

        {{-- Module Overview --}}
        <details class="card xl:col-span-2 group">
            <summary class="flex cursor-pointer list-none items-center justify-between gap-4 p-5">
                <div>
                    <h3 class="text-base font-semibold text-slate-900">System modules</h3>
                    <p class="mt-0.5 text-sm text-slate-500">Browse the full MCS Purchasing &amp; Inventory System.</p>
                </div><i data-lucide="chevron-down" class="h-5 w-5 text-slate-400 transition-transform duration-150 group-open:rotate-180"></i>
            </summary>
            <div class="grid grid-cols-1 gap-px border-t border-slate-200 bg-slate-200 sm:grid-cols-2 lg:grid-cols-3">

                @php
                $modules = [
                // Purchasing
                ['group' => 'Purchasing', 'icon' => 'file-text', 'name' => 'GE Orders', 'desc' => 'Create and track purchase requests', 'route' => 'ge-orders.index'],
                ['group' => 'Purchasing', 'icon' => 'truck', 'name' => 'Suppliers', 'desc' => 'Manage supplier records', 'route' => 'suppliers.index'],
                ['group' => 'Purchasing', 'icon' => 'package-check', 'name' => 'Purchase Receipts', 'desc' => 'Record goods received from suppliers', 'route' => 'receiving.index'],
                // Inventory
                ['group' => 'Inventory', 'icon' => 'boxes', 'name' => 'Items', 'desc' => 'Stock and non-stock item catalog', 'route' => 'inventory.index'],
                ['group' => 'Inventory', 'icon' => 'bar-chart-2', 'name' => 'Stock Levels', 'desc' => 'Monitor current stock quantities', 'route' => 'inventory.index'],
                ['group' => 'Inventory', 'icon' => 'minus-circle', 'name' => 'Stock Issues', 'desc' => 'Issue stock to departments and users', 'route' => 'inventory.issues.index'],
                ['group' => 'Inventory', 'icon' => 'sliders-horizontal', 'name' => 'Stock Adjustments', 'desc' => 'Adjust stock levels with audit trail', 'route' => 'inventory.adjustments.index'],
                ['group' => 'Inventory', 'icon' => 'users', 'name' => 'Customers', 'desc' => 'Manage customer records', 'route' => 'customers.index'],
                ['group' => 'Inventory', 'icon' => 'building-2', 'name' => 'Branches', 'desc' => 'Manage branch locations', 'route' => 'branches.index'],
                // Assets
                ['group' => 'Assets', 'icon' => 'package', 'name' => 'Asset Register', 'desc' => 'Register and track fixed assets', 'route' => 'assets.index'],
                // Admin
                ['group' => 'Administration', 'icon' => 'shield-users', 'name' => 'Users & Roles', 'desc' => 'Manage users and permissions', 'route' => 'users.index'],
                ];
                $groupColors = [
                'Purchasing' => 'bg-brand-50 text-brand-600',
                'Inventory' => 'bg-emerald-50 text-emerald-600',
                'Assets' => 'bg-violet-50 text-violet-600',
                'Administration' => 'bg-slate-100 text-slate-600',
                ];
                @endphp

                @foreach ($modules as $module)
                <a href="{{ $safeRoute($module['route']) }}" class="group flex items-start gap-3 bg-white p-4 transition-colors hover:bg-slate-50">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg {{ $groupColors[$module['group']] ?? 'bg-slate-100 text-slate-600' }}">
                        <i data-lucide="{{ $module['icon'] }}" class="h-5 w-5"></i>
                    </span>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-slate-900 group-hover:text-brand-700">{{ $module['name'] }}</p>
                        <p class="mt-0.5 text-xs leading-snug text-slate-500">{{ $module['desc'] }}</p>
                        <p class="mt-1 text-[10px] font-semibold uppercase tracking-wider text-slate-400">{{ $module['group'] }}</p>
                    </div>
                </a>
                @endforeach
            </div>
        </details>

        {{-- Reporting + Recent Activity --}}
        <div class="flex flex-col gap-6">
            {{-- Reporting --}}
            <section class="card">
                <div class="border-b border-slate-200 p-5">
                    <h3 class="text-base font-semibold text-slate-900">Reports</h3>
                    <p class="mt-0.5 text-sm text-slate-500">Management &amp; operational insights</p>
                </div>
                <div class="space-y-3 p-5">
                    <div class="rounded-lg border border-slate-200 p-4 transition-colors hover:border-brand-300 hover:bg-brand-50/30">
                        <div class="flex items-start gap-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-600"><i data-lucide="trending-up" class="h-4 w-4"></i></span>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-slate-900">Management Reports</p>
                                <p class="mt-0.5 text-xs leading-snug text-slate-500">Procurement spending, purchasing activity and inventory value.</p>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-lg border border-slate-200 p-4 transition-colors hover:border-emerald-300 hover:bg-emerald-50/30">
                        <div class="flex items-start gap-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600"><i data-lucide="activity" class="h-4 w-4"></i></span>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-slate-900">Operational Reports</p>
                                <p class="mt-0.5 text-xs leading-snug text-slate-500">Stock movements, receipts, issues and adjustments.</p>
                            </div>
                        </div>
                    </div>
                    <a href="{{ $safeRoute('reports.index') }}" class="btn btn-secondary w-full">
                        View Reports
                        <i data-lucide="arrow-right" class="h-4 w-4"></i>
                    </a>
                </div>
            </section>

            {{-- Recent Activity (compact) --}}
            <section class="card flex-1">
                <div class="flex items-center justify-between border-b border-slate-200 p-5">
                    <h3 class="text-base font-semibold text-slate-900">Recent Activity</h3>
                    <a href="{{ $safeRoute('reports.activity') }}" class="text-sm font-medium text-brand-600 hover:text-brand-700">View all</a>
                </div>
                <div class="relative p-5">
                    <div class="absolute left-[26px] top-5 bottom-5 w-px bg-slate-100"></div>
                    @foreach ($recentActivity as $activity)
                    <x-activity-item
                        :icon="$activity->icon"
                        :icon-color="$activity->icon_color"
                        :actor="$activity->actor"
                        :action="$activity->action"
                        :time="$activity->time" />
                    @endforeach
                    @if ($recentActivity->isEmpty())
                    <p class="pl-1 text-sm text-slate-500">No recent activity to show.</p>
                    @endif
                </div>
            </section>
        </div>
    </div>

</x-app-layout>