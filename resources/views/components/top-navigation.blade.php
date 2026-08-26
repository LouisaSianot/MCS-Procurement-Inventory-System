<header class="sticky top-0 z-20 flex h-16 items-center gap-3 border-b border-slate-200 bg-white/90 px-4 backdrop-blur sm:px-6 lg:px-8">
    {{-- Mobile sidebar toggle --}}
    <button id="mobile-sidebar-toggle" type="button"
        class="rounded-md p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-700 lg:hidden">
        <i data-lucide="menu" class="h-5 w-5"></i>
        <span class="sr-only">Open menu</span>
    </button>

    {{-- Page title --}}
    <h1 class="text-lg font-semibold text-slate-900 sm:text-xl">{{ $title ?? 'Dashboard' }}</h1>

    {{-- Search (hidden on small screens) --}}
    <div class="relative ml-auto hidden md:block">
        <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"></i>
        <input type="search" name="search" placeholder="Search orders, items, suppliers…"
            class="w-64 rounded-lg border border-slate-300 bg-slate-50 py-2 pl-9 pr-3 text-sm text-slate-700 placeholder-slate-400 transition-colors focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20 lg:w-80">
    </div>

    {{-- Notifications --}}
    <div class="relative ml-auto md:ml-3">
        <button type="button"
            class="relative rounded-lg p-2 text-slate-500 transition-colors hover:bg-slate-100 hover:text-slate-700"
            aria-label="Notifications">
            <i data-lucide="bell" class="h-5 w-5"></i>
            @if (isset($unreadNotifications) && $unreadNotifications > 0)
            <span class="absolute right-1.5 top-1.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-bold text-white">
                {{ $unreadNotifications }}
            </span>
            @endif
        </button>
    </div>

    {{-- User avatar (quick dropdown trigger) --}}
    @auth
    <div class="relative ml-2">
        <button type="button" class="flex items-center gap-2 rounded-lg p-1 pr-2 transition-colors hover:bg-slate-100">
            @php
            $initials = strtoupper(implode('', array_map(
            fn($w) => substr($w, 0, 1),
            explode(' ', trim(Auth::user()->name ?? 'Jane Doe'))
            )));
            @endphp
            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-100 text-xs font-semibold text-brand-700">
                {{ $initials }}
            </span>
            <span class="hidden text-sm font-medium text-slate-700 sm:block">
                {{ Auth::user()->name }}
            </span>
            <i data-lucide="chevron-down" class="hidden h-4 w-4 text-slate-400 sm:block"></i>
        </button>
    </div>
    @else
    <div class="relative ml-2">
        <button type="button" class="flex items-center gap-2 rounded-lg p-1 pr-2 transition-colors hover:bg-slate-100">
            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-100 text-xs font-semibold text-brand-700">JD</span>
            <span class="hidden text-sm font-medium text-slate-700 sm:block">Jane Doe</span>
            <i data-lucide="chevron-down" class="hidden h-4 w-4 text-slate-400 sm:block"></i>
        </button>
    </div>
    @endauth
</header>