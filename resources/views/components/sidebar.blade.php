@php
// Default demo user — in production this comes from Auth::user().
$currentUser = $user ?? (object) [
'name' => 'Jane Doe',
'role' => 'Procurement Officer',
'initials' => 'JD',
];
$initials = $currentUser->initials ?? implode('', array_map(
fn($w) => strtoupper(substr($w, 0, 1)),
explode(' ', trim($currentUser->name))
));

// Active route key. Each nav item is marked active when its route matches.
$current = request()->route() ? ltrim(request()->route()->getName(), '.') : '';
@endphp

{{-- Mobile backdrop --}}
<div id="sidebar-backdrop"
    class="fixed inset-0 z-30 hidden bg-slate-900/50 backdrop-blur-sm lg:hidden"></div>

<aside id="sidebar"
    class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col border-r border-slate-200 bg-white transition-transform duration-200 ease-in-out lg:translate-x-0">

    {{-- Brand / logo --}}
    <div class="flex h-16 shrink-0 items-center justify-between gap-2 border-b border-slate-200 px-4">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 overflow-hidden">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-brand-600 text-white shadow-sm">
                <i data-lucide="layers-3" class="h-5 w-5"></i>
            </span>
            <span class="collapsible-label whitespace-nowrap text-[15px] font-bold tracking-tight text-slate-900">
                Procure<span class="text-brand-600">Desk</span>
            </span>
        </a>

        {{-- Close button (mobile only) --}}
        <button id="sidebar-close" type="button"
            class="rounded-md p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 lg:hidden">
            <i data-lucide="x" class="h-5 w-5"></i>
            <span class="sr-only">Close menu</span>
        </button>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 overflow-y-auto px-3 py-4">
        <p class="collapsible-label px-3 pb-2 text-[11px] font-semibold uppercase tracking-wider text-slate-400">
            Main
        </p>
        <ul class="space-y-1">
            <li>
                <a href="{{ route('dashboard') }}" aria-current="{{ $current === 'dashboard' ? 'page' : 'false' }}" class="nav-link">
                    <i data-lucide="layout-dashboard" class="nav-link-icon"></i>
                    <span class="collapsible-label">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="{{ route('ge-orders.index') }}" aria-current="{{ str_starts_with($current, 'ge-orders') ? 'page' : 'false' }}" class="nav-link">
                    <i data-lucide="file-text" class="nav-link-icon"></i>
                    <span class="collapsible-label">GE Orders</span>
                </a>
            </li>
            <li>
                <a href="{{ route('procurement.index') }}" aria-current="{{ str_starts_with($current, 'procurement') ? 'page' : 'false' }}" class="nav-link">
                    <i data-lucide="shopping-cart" class="nav-link-icon"></i>
                    <span class="collapsible-label">Purchase Orders</span>
                </a>
            </li>
            <li>
                <a href="{{ route('receiving.index') }}" aria-current="{{ str_starts_with($current, 'receiving') ? 'page' : 'false' }}" class="nav-link">
                    <i data-lucide="package-check" class="nav-link-icon"></i>
                    <span class="collapsible-label">Receiving</span>
                </a>
            </li>

            @if (Route::has('suppliers.index'))
            <li>
                <a href="{{ route('suppliers.index') }}" aria-current="{{ str_starts_with($current, 'suppliers') ? 'page' : 'false' }}" class="nav-link">
                    <i data-lucide="truck" class="nav-link-icon"></i>
                    <span class="collapsible-label">Suppliers</span>
                </a>
            </li>
            @endif

            <li>
                <a href="{{ route('inventory.index') }}" aria-current="{{ str_starts_with($current, 'inventory') ? 'page' : 'false' }}" class="nav-link">
                    <i data-lucide="boxes" class="nav-link-icon"></i>
                    <span class="collapsible-label">Inventory</span>
                </a>
            </li>
            <li>
                <a href="{{ route('reports.index') }}" aria-current="{{ str_starts_with($current, 'reports') ? 'page' : 'false' }}" class="nav-link">
                    <i data-lucide="bar-chart-3" class="nav-link-icon"></i>
                    <span class="collapsible-label">Reports</span>
                </a>
            </li>
        </ul>

        <p class="collapsible-label mt-6 px-3 pb-2 text-[11px] font-semibold uppercase tracking-wider text-slate-400">
            System
        </p>
        <ul class="space-y-1">

            @if (Route::has('assets.index'))
            <li>
                <a href="{{ route('assets.index') }}" aria-current="{{ str_starts_with($current, 'assets') ? 'page' : 'false' }}" class="nav-link">
                    <i data-lucide="package" class="nav-link-icon"></i>
                    <span class="collapsible-label">Assets</span>
                </a>
            </li>
            @endif
            @if (Route::has('users.index'))
            <li>
                <a href="{{ route('users.index') }}" aria-current="{{ str_starts_with($current, 'users') ? 'page' : 'false' }}" class="nav-link">
                    <i data-lucide="users" class="nav-link-icon"></i>
                    <span class="collapsible-label">Users &amp; Roles</span>
                </a>
            </li>
            @endif

        </ul>
    </nav>

    {{-- User profile + logout --}}
    <div class="border-t border-slate-200 p-3">
        <div class="flex items-center gap-3 rounded-lg px-2 py-2">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-brand-100 text-sm font-semibold text-brand-700">
                {{ $initials }}
            </span>
            <div class="collapsible-label min-w-0 flex-1">
                <p class="truncate text-sm font-semibold text-slate-800">{{ $currentUser->name }}</p>
                <p class="truncate text-xs text-slate-500">{{ $currentUser->role }}</p>
            </div>
        </div>

        {{-- Logout — Laravel POST form (route('logout') is provided by auth routes) --}}
        <form method="POST" action="{{ route('logout') }}" class="mt-2">
            @csrf
            <button type="submit"
                class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition-colors hover:bg-rose-50 hover:text-rose-600">
                <i data-lucide="log-out" class="h-5 w-5 text-slate-400"></i>
                <span class="collapsible-label">Logout</span>
            </button>
        </form>
    </div>

    {{-- Desktop collapse toggle --}}
    <button id="sidebar-collapse" type="button"
        class="absolute -right-3 top-20 hidden h-6 w-6 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-400 shadow-sm hover:text-brand-600 lg:flex">
        <i data-lucide="chevrons-left" class="h-3.5 w-3.5"></i>
    </button>
</aside>
