<x-app-layout>
    <div class="min-h-screen bg-gradient-to-br from-sky-50 via-blue-50 to-white">

        @php
        /* ---------------------------------------------------------------
        | Dashboard — all data comes from the controller.
        | Fallback demo values are used only when variables are unset so
        | the view renders sensibly during development.
        |--------------------------------------------------------------- */
        $totalOrders = $totalOrders ?? 0;
        $ordersChange = $ordersChange ?? null;
        $ordersChangeDir = $ordersChangeDir ?? 'up';

        $pendingApprovals = $pendingApprovals ?? 0;

        $totalPurchases = $totalPurchases ?? 56;
        $purchasesChange = $purchasesChange ?? '+8%';
        $purchasesChangeDir = $purchasesChangeDir ?? 'up';

        $totalInventoryItems = $totalInventoryItems ?? 342;

        $lowStockItems = $lowStockItems ?? 18;
        $lowStockChange = $lowStockChange ?? '+3';
        $lowStockChangeDir = $lowStockChangeDir ?? 'up';

        $totalExpenditure = $totalExpenditure ?? 'K 245,850';
        $expenditureChange = $expenditureChange ?? '+5.2%';
        $expenditureChangeDir= $expenditureChangeDir?? 'up';

        // Collections from the controller, with demo fallbacks.
        $inventoryItems = $inventoryItems ?? collect([
        (object)['id'=>1,'name'=>'A4 Paper','category'=>'Office Supplies','quantity'=>25,'minimum_stock'=>10,'status'=>'In Stock','supplier'=>'PNG Office Supplies','last_received'=>'10 Aug 2026'],
        (object)['id'=>2,'name'=>'Printer Toner','category'=>'IT Supplies','quantity'=>4,'minimum_stock'=>10,'status'=>'Low Stock','supplier'=>'Tech Supplies Ltd','last_received'=>'8 Aug 2026'],
        (object)['id'=>3,'name'=>'Blue Pens','category'=>'Office Supplies','quantity'=>0,'minimum_stock'=>20,'status'=>'Out of Stock','supplier'=>'PNG Office Supplies','last_received'=>'2 Aug 2026'],
        (object)['id'=>4,'name'=>'Stapler Heavy Duty','category'=>'Office Supplies','quantity'=>12,'minimum_stock'=>5,'status'=>'In Stock','supplier'=>'Office Gear Co','last_received'=>'6 Aug 2026'],
        (object)['id'=>5,'name'=>'USB-C Cables','category'=>'IT Supplies','quantity'=>3,'minimum_stock'=>8,'status'=>'Low Stock','supplier'=>'Tech Supplies Ltd','last_received'=>'1 Aug 2026'],
        (object)['id'=>6,'name'=>'First Aid Kit','category'=>'Health & Safety','quantity'=>7,'minimum_stock'=>4,'status'=>'In Stock','supplier'=>'Medi Supplies PNG','last_received'=>'28 Jul 2026'],
        ]);

        $recentOrders = $recentOrders ?? collect();

        $recentPurchases = $recentPurchases ?? collect([
        (object)['id'=>1,'number'=>'PO-0045','ge_order'=>'GE-00120','supplier'=>'PNG Office Supplies','date'=>'6 Aug 2026','amount'=>'K 23,400','status'=>'Partially Received'],
        (object)['id'=>2,'number'=>'PO-0044','ge_order'=>'GE-00124','supplier'=>'Tech Supplies Ltd','date'=>'9 Aug 2026','amount'=>'K 12,200','status'=>'Ordered'],
        (object)['id'=>3,'number'=>'PO-0042','ge_order'=>'GE-00118','supplier'=>'Office Gear Co','date'=>'1 Aug 2026','amount'=>'K 3,900','status'=>'Fully Received'],
        (object)['id'=>4,'number'=>'PO-0041','ge_order'=>'GE-00117','supplier'=>'Medi Supplies PNG','date'=>'30 Jul 2026','amount'=>'K 2,100','status'=>'Completed'],
        ]);

        $inventoryAlerts = $inventoryAlerts ?? collect([
        (object)['type'=>'Low Stock','severity'=>'warning','icon'=>'alert-triangle','title'=>'Printer Toner','message'=>'has reached its minimum stock level.'],
        (object)['type'=>'Out of Stock','severity'=>'error','icon'=>'x-circle','title'=>'Blue Pens','message'=>'are currently out of stock.'],
        (object)['type'=>'Pending Delivery','severity'=>'info','icon'=>'truck','title'=>'30 boxes of A4 Paper','message'=>'are still outstanding.'],
        ]);

        $recentActivity = $recentActivity ?? collect([
        (object)['icon'=>'file-plus','icon_color'=>'brand','actor'=>'John Smith','action'=>'created GE Order GE-00125','time'=>'10 minutes ago'],
        (object)['icon'=>'check-circle','icon_color'=>'emerald','actor'=>'Head of School','action'=>'approved GE-00120','time'=>'1 hour ago'],
        (object)['icon'=>'package-check','icon_color'=>'sky','actor'=>'Mary Doe','action'=>'received 20 boxes of A4 Paper','time'=>'3 hours ago'],
        (object)['icon'=>'shopping-cart','icon_color'=>'violet','actor'=>'Procurement Officer','action'=>'created Purchase PO-0045','time'=>'Yesterday'],
        (object)['icon'=>'alert-triangle','icon_color'=>'amber','actor'=>'System','action'=>'flagged USB-C Cables as low stock','time'=>'Yesterday'],
        ]);
        @endphp

        {{-- ============================================================= --}}
        {{-- Page heading + quick actions                                  --}}
        {{-- ============================================================= --}}
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold tracking-tight text-slate-900">Welcome back, {{ Auth::user()->name ?? 'Jane' }}</h2>
                <p class="mt-1 text-sm text-slate-500">Here's what's happening across procurement &amp; inventory today.</p>
            </div>
            @can('ge-orders.create')
            <a href="{{ route('ge-orders.create') }}" class="btn btn-primary">
                <i data-lucide="plus" class="h-4 w-4"></i>
                Create GE Order
            </a>
            @endcan
        </div>

        {{-- ============================================================= --}}
        {{-- Statistic cards                                              --}}
        {{-- ============================================================= --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
            <x-stat-card title="Total GE Orders" :value="$totalOrders" icon="file-text" icon-color="brand" :change="$ordersChange" :change-direction="$ordersChangeDir" hint="this month" />
            <x-stat-card title="Pending Approval" :value="$pendingApprovals" icon="clock" icon-color="amber" />
            <x-stat-card title="Total Purchases" :value="$totalPurchases" icon="shopping-cart" icon-color="violet" :change="$purchasesChange" :change-direction="$purchasesChangeDir" hint="this month" />
            <x-stat-card title="Inventory Items" :value="$totalInventoryItems" icon="boxes" icon-color="sky" />
            <x-stat-card title="Low Stock Items" :value="$lowStockItems" icon="alert-triangle" icon-color="rose" :change="$lowStockChange" :change-direction="$lowStockChangeDir" hint="vs last week" />
            <x-stat-card title="Total Expenditure" :value="$totalExpenditure" icon="wallet" icon-color="emerald" :change="$expenditureChange" :change-direction="$expenditureChangeDir" hint="this month" />
        </div>

        {{-- ============================================================= --}}
        {{-- Inventory overview                                           --}}
        {{-- ============================================================= --}}
        <section class="mt-6 card animate-fade-in">
            <div class="flex flex-col gap-4 border-b border-slate-200 p-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h3 class="text-base font-semibold text-slate-900">Inventory Overview</h3>
                    <p class="mt-0.5 text-sm text-slate-500">{{ $inventoryItems->count() }} items in stock</p>
                </div>

                {{-- Filters --}}
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <div class="relative">
                        <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"></i>
                        <input id="inventory-search" type="search" placeholder="Search items…"
                            class="input pl-9 sm:w-56">
                    </div>
                    <select id="inventory-category-filter" class="input sm:w-44">
                        <option value="">All categories</option>
                        @foreach ($inventoryItems->pluck('category')->unique() as $cat)
                        <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>
                    <select id="inventory-status-filter" class="input sm:w-40">
                        <option value="">All statuses</option>
                        <option value="in stock">In Stock</option>
                        <option value="low stock">Low Stock</option>
                        <option value="out of stock">Out of Stock</option>
                    </select>
                </div>
            </div>

            {{-- Table (desktop) --}}
            <div class="table-wrap">
                <table class="data-table hidden md:table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Category</th>
                            <th class="text-right">Quantity</th>
                            <th class="text-right">Min Stock</th>
                            <th>Status</th>
                            <th>Supplier</th>
                            <th>Last Received</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($inventoryItems as $item)
                        @php $rowStatus = strtolower($item->status); @endphp
                        <tr data-inventory-row data-category="{{ $item->category }}" data-status="{{ $rowStatus }}">
                            <td class="font-medium text-slate-900">{{ $item->name }}</td>
                            <td>{{ $item->category }}</td>
                            <td class="text-right tabular-nums">{{ $item->quantity }}</td>
                            <td class="text-right tabular-nums text-slate-500">{{ $item->minimum_stock }}</td>
                            <td><x-status-badge :status="$item->status" /></td>
                            <td>{{ $item->supplier }}</td>
                            <td class="text-slate-500">{{ $item->last_received }}</td>
                            <td class="text-right">
                                <a href="{{ route('inventory.show', $item->id ?? 1) }}" class="inline-flex items-center gap-1 text-sm font-medium text-brand-600 hover:text-brand-700">
                                    View <i data-lucide="arrow-right" class="h-3.5 w-3.5"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- Card layout (mobile) --}}
                <div class="divide-y divide-slate-100 md:hidden">
                    @foreach ($inventoryItems as $item)
                    @php $rowStatus = strtolower($item->status); @endphp
                    <div data-inventory-row data-category="{{ $item->category }}" data-status="{{ $rowStatus }}" class="p-4">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <p class="font-semibold text-slate-900">{{ $item->name }}</p>
                                <p class="text-xs text-slate-500">{{ $item->category }} · {{ $item->supplier }}</p>
                            </div>
                            <x-status-badge :status="$item->status" />
                        </div>
                        <div class="mt-3 flex items-center justify-between text-sm">
                            <span class="text-slate-500">Qty: <span class="font-semibold text-slate-800">{{ $item->quantity }}</span> / min {{ $item->minimum_stock }}</span>
                            <a href="{{ route('inventory.show', $item->id ?? 1) }}" class="font-medium text-brand-600">View</a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center justify-between border-t border-slate-200 p-4">
                <p class="text-xs text-slate-400">Showing {{ $inventoryItems->count() }} items</p>
                <a href="{{ route('inventory.index') }}" class="btn btn-secondary py-2">
                    View All Inventory
                    <i data-lucide="arrow-right" class="h-4 w-4"></i>
                </a>
            </div>
        </section>

        {{-- ============================================================= --}}
        {{-- Two-column: Recent GE Orders | Recent Purchases              --}}
        {{-- ============================================================= --}}
        <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-2">

            {{-- Recent GE Orders --}}
            <section class="card animate-fade-in">
                <div class="flex items-center justify-between border-b border-slate-200 p-5">
                    <div>
                        <h3 class="text-base font-semibold text-slate-900">Recent GE Orders</h3>
                        <p class="mt-0.5 text-sm text-slate-500">Latest purchase requests</p>
                    </div>
                    <a href="{{ route('ge-orders.index') }}" class="text-sm font-medium text-brand-600 hover:text-brand-700">View all</a>
                </div>
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Requester</th>
                                <th>Date</th>
                                <th class="text-right">Amount</th>
                                <th>Status</th>
                                <th>Approval</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentOrders as $order)
                            <tr>
                                <td class="font-mono text-xs font-semibold text-slate-900">{{ $order->order_number }}</td>
                                <td>{{ $order->requester?->name ?? 'Unknown requester' }}</td>
                                <td class="text-slate-500">{{ optional($order->order_date)->format('d M Y') }}</td>
                                <td class="text-right font-medium tabular-nums">K {{ number_format((float) $order->total_amount, 2) }}</td>
                                <td><x-status-badge :status="$order->status" /></td>
                                <td><x-status-badge :status="$order->approval_status" /></td>
                                <td class="text-right">
                                    <a href="{{ route('ge-orders.show', $order) }}" class="inline-flex items-center gap-1 text-sm font-medium text-brand-600 hover:text-brand-700">
                                        View <i data-lucide="arrow-right" class="h-3.5 w-3.5"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                            @if ($recentOrders->isEmpty())
                            <tr>
                                <td colspan="7" class="px-5 py-10 text-center text-sm text-slate-500">No GE orders have been created yet.</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </section>

            {{-- Recent Purchases --}}
            <section class="card animate-fade-in">
                <div class="flex items-center justify-between border-b border-slate-200 p-5">
                    <div>
                        <h3 class="text-base font-semibold text-slate-900">Recent Purchases</h3>
                        <p class="mt-0.5 text-sm text-slate-500">Latest purchase orders</p>
                    </div>
                    <a href="{{ route('procurement.index') }}" class="text-sm font-medium text-brand-600 hover:text-brand-700">View all</a>
                </div>
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Purchase #</th>
                                <th>GE Order</th>
                                <th>Supplier</th>
                                <th>Date</th>
                                <th class="text-right">Amount</th>
                                <th>Status</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentPurchases as $purchase)
                            <tr>
                                <td class="font-mono text-xs font-semibold text-slate-900">{{ $purchase->number }}</td>
                                <td class="font-mono text-xs">{{ $purchase->ge_order }}</td>
                                <td>{{ $purchase->supplier }}</td>
                                <td class="text-slate-500">{{ $purchase->date }}</td>
                                <td class="text-right font-medium tabular-nums">{{ $purchase->amount }}</td>
                                <td><x-status-badge :status="$purchase->status" /></td>
                                <td class="text-right">
                                    <a href="{{ route('procurement.show', $purchase->id ?? 1) }}" class="inline-flex items-center gap-1 text-sm font-medium text-brand-600 hover:text-brand-700">
                                        View <i data-lucide="arrow-right" class="h-3.5 w-3.5"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        {{-- ============================================================= --}}
        {{-- Three-column: Alerts | Quick Actions | Recent Activity       --}}
        {{-- ============================================================= --}}
        <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">

            {{-- Inventory Alerts --}}
            <section class="card animate-fade-in lg:col-span-1">
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

            {{-- Quick Actions --}}
            <section class="card animate-fade-in lg:col-span-1">
                <div class="border-b border-slate-200 p-5">
                    <h3 class="text-base font-semibold text-slate-900">Quick Actions</h3>
                    <p class="mt-0.5 text-sm text-slate-500">Common workflows</p>
                </div>
                <div class="grid grid-cols-1 gap-3 p-5 sm:grid-cols-2 lg:grid-cols-1">
                    <a href="{{ route('ge-orders.create') }}" class="group flex items-center gap-3 rounded-lg border border-slate-200 p-3 transition-all hover:border-brand-300 hover:bg-brand-50/50">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-50 text-brand-600 group-hover:bg-brand-100"><i data-lucide="file-plus-2" class="h-4 w-4"></i></span>
                        <span class="text-sm font-medium text-slate-700 group-hover:text-brand-700">Create GE Order</span>
                        <i data-lucide="arrow-right" class="ml-auto h-4 w-4 text-slate-300 group-hover:text-brand-500"></i>
                    </a>
                    <a href="{{ route('ge-orders.index', ['status' => 'pending']) }}" class="group flex items-center gap-3 rounded-lg border border-slate-200 p-3 transition-all hover:border-amber-300 hover:bg-amber-50/50">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-50 text-amber-600 group-hover:bg-amber-100"><i data-lucide="clock-3" class="h-4 w-4"></i></span>
                        <span class="text-sm font-medium text-slate-700 group-hover:text-amber-700">Pending Approvals</span>
                        <i data-lucide="arrow-right" class="ml-auto h-4 w-4 text-slate-300 group-hover:text-amber-500"></i>
                    </a>
                    <a href="{{ route('procurement.create') }}" class="group flex items-center gap-3 rounded-lg border border-slate-200 p-3 transition-all hover:border-violet-300 hover:bg-violet-50/50">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-violet-50 text-violet-600 group-hover:bg-violet-100"><i data-lucide="shopping-cart" class="h-4 w-4"></i></span>
                        <span class="text-sm font-medium text-slate-700 group-hover:text-violet-700">Record Purchase</span>
                        <i data-lucide="arrow-right" class="ml-auto h-4 w-4 text-slate-300 group-hover:text-violet-500"></i>
                    </a>
                    <a href="{{ route('receiving.index') }}" class="group flex items-center gap-3 rounded-lg border border-slate-200 p-3 transition-all hover:border-sky-300 hover:bg-sky-50/50">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-sky-50 text-sky-600 group-hover:bg-sky-100"><i data-lucide="package-check" class="h-4 w-4"></i></span>
                        <span class="text-sm font-medium text-slate-700 group-hover:text-sky-700">Receive Items</span>
                        <i data-lucide="arrow-right" class="ml-auto h-4 w-4 text-slate-300 group-hover:text-sky-500"></i>
                    </a>
                    <a href="{{ route('inventory.create') }}" class="group flex items-center gap-3 rounded-lg border border-slate-200 p-3 transition-all hover:border-emerald-300 hover:bg-emerald-50/50">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 group-hover:bg-emerald-100"><i data-lucide="plus-box" class="h-4 w-4"></i></span>
                        <span class="text-sm font-medium text-slate-700 group-hover:text-emerald-700">Add Inventory Item</span>
                        <i data-lucide="arrow-right" class="ml-auto h-4 w-4 text-slate-300 group-hover:text-emerald-500"></i>
                    </a>
                    <a href="{{ route('reports.index') }}" class="group flex items-center gap-3 rounded-lg border border-slate-200 p-3 transition-all hover:border-slate-400 hover:bg-slate-50">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 text-slate-600 group-hover:bg-slate-200"><i data-lucide="file-bar-chart" class="h-4 w-4"></i></span>
                        <span class="text-sm font-medium text-slate-700 group-hover:text-slate-900">Generate Report</span>
                        <i data-lucide="arrow-right" class="ml-auto h-4 w-4 text-slate-300 group-hover:text-slate-500"></i>
                    </a>
                </div>
            </section>

            {{-- Recent Activity --}}
            <section class="card animate-fade-in lg:col-span-1">
                <div class="flex items-center justify-between border-b border-slate-200 p-5">
                    <h3 class="text-base font-semibold text-slate-900">Recent Activity</h3>
                    <a href="{{ route('reports.activity') }}" class="text-sm font-medium text-brand-600 hover:text-brand-700">View all</a>
                </div>
                <div class="relative p-5">
                    {{-- Timeline spine --}}
                    <div class="absolute left-[26px] top-5 bottom-5 w-px bg-slate-100"></div>
                    @foreach ($recentActivity as $activity)
                    <x-activity-item
                        :icon="$activity->icon"
                        :icon-color="$activity->icon_color"
                        :actor="$activity->actor"
                        :action="$activity->action"
                        :time="$activity->time" />
                    @endforeach
                </div>
            </section>
        </div>
    </div>

</x-app-layout>