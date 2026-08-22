<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>MCS Purchasing &amp; Inventory System</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-white font-sans text-slate-800 antialiased">

    {{-- ===== Fixed top navigation bar ===== --}}
    <nav class="fixed inset-x-0 top-0 z-50 border-b border-slate-200 bg-white/90 backdrop-blur-md">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
            {{-- Left: App branding --}}
            <a href="{{ route('welcome') }}" class="flex items-center gap-2.5">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-brand-600 text-white shadow-sm">
                    <i data-lucide="package" class="h-5 w-5"></i>
                </span>
                <span class="whitespace-nowrap text-[15px] font-bold tracking-tight text-slate-900">
                    MCS Purchasing &amp; Inventory System
                </span>
            </a>

            {{-- Right: CTA buttons (desktop) --}}
            <div class="hidden items-center gap-3 sm:flex">
                <a href="{{ route('login') }}" class="btn btn-secondary">
                    Log In
                </a>
                <a href="{{ route('register') }}" class="btn btn-primary">
                    Sign Up
                </a>
            </div>

            {{-- Mobile menu toggle --}}
            <button id="mobile-menu-toggle" type="button" class="rounded-md p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-700 sm:hidden">
                <i data-lucide="menu" class="h-5 w-5"></i>
                <span class="sr-only">Open menu</span>
            </button>
        </div>

        {{-- Mobile dropdown --}}
        <div id="mobile-menu" class="hidden border-t border-slate-200 bg-white px-4 py-3 sm:hidden">
            <div class="flex flex-col gap-2.5">
                <a href="{{ route('login') }}" class="btn btn-secondary w-full">Log In</a>
                <a href="{{ route('register') }}" class="btn btn-primary w-full">Sign Up</a>
            </div>
        </div>
    </nav>

    {{-- ===== Main hero section ===== --}}
    <main class="relative flex min-h-full items-center justify-center overflow-hidden pt-16">
        {{-- Soft gradient background: light blue → white --}}
        <div class="absolute inset-0 bg-gradient-to-b from-brand-50 via-white to-white"></div>

        {{-- Decorative blurred orbs --}}
        <div class="absolute -left-24 top-24 h-72 w-72 rounded-full bg-brand-100/40 blur-3xl"></div>
        <div class="absolute -right-24 bottom-24 h-72 w-72 rounded-full bg-sky-100/40 blur-3xl"></div>

        {{-- Content --}}
        <div class="relative z-10 mx-auto flex max-w-3xl flex-col items-center px-6 py-20 text-center sm:py-28 lg:py-32">

            {{-- System badge --}}
            <span class="inline-flex items-center gap-2 rounded-full border border-brand-200 bg-brand-50 px-4 py-1.5 text-xs font-semibold text-brand-700 shadow-sm animate-fade-in">
                <i data-lucide="shield-check" class="h-3.5 w-3.5"></i>
                PNG University of Technology · School of MCS
            </span>

            {{-- Hero heading --}}
            <h1 class="mt-6 text-3xl font-bold leading-tight tracking-tight text-slate-900 animate-fade-in sm:text-4xl lg:text-5xl lg:leading-[1.1]">
                Welcome to the MCS Purchasing &amp; Inventory Management System
            </h1>

            {{-- Description --}}
            <p class="mt-6 max-w-2xl text-base leading-relaxed text-slate-600 animate-fade-in sm:text-lg">
                Streamlining operations for the PNG University of Technology School of MCS.
                Efficiently manage purchase requests (GE Orders), track stock and non-stock items,
                monitor inventory movements, and handle asset registrations — all in one secure,
                unified database system.
            </p>

            {{-- Action callout --}}
            <p class="mt-8 max-w-xl text-sm font-medium text-slate-700 animate-fade-in sm:text-base">
                Please log in with your credentials or sign up for account access to continue.
            </p>

            {{-- Primary action buttons --}}
            <div class="mt-8 flex w-full flex-col items-center gap-3 animate-fade-in sm:w-auto sm:flex-row sm:gap-4">
                <a href="{{ route('login') }}" class="btn btn-primary w-full px-6 py-3 text-base sm:w-auto">
                    Log In to Dashboard
                    <i data-lucide="arrow-right" class="h-4 w-4"></i>
                </a>
                <a href="{{ route('register') }}" class="btn btn-secondary w-full px-6 py-3 text-base sm:w-auto">
                    Sign Up / Request Access
                </a>
            </div>

            {{-- Feature highlights --}}
            <div class="mt-16 grid w-full grid-cols-1 gap-4 sm:grid-cols-3 sm:gap-6">
                @php
                    $features = [
                        ['icon' => 'file-text',     'title' => 'GE Orders',       'desc' => 'Create, submit, and track purchase requests through a multi-step approval workflow.'],
                        ['icon' => 'boxes',         'title' => 'Inventory',        'desc' => 'Monitor stock and non-stock items with real-time quantity and movement tracking.'],
                        ['icon' => 'package-check', 'title' => 'Procurement',      'desc' => 'Manage suppliers, purchase orders, and receiving all in one centralized system.'],
                    ];
                @endphp
                @foreach ($features as $feature)
                    <div class="card card-hover animate-fade-in p-5 text-left">
                        <span class="flex h-11 w-11 items-center justify-center rounded-lg bg-brand-50 text-brand-600">
                            <i data-lucide="{{ $feature['icon'] }}" class="h-5 w-5"></i>
                        </span>
                        <h3 class="mt-4 text-sm font-semibold text-slate-900">{{ $feature['title'] }}</h3>
                        <p class="mt-1.5 text-sm leading-relaxed text-slate-500">{{ $feature['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </main>

    {{-- ===== Footer ===== --}}
    <footer class="relative z-10 border-t border-slate-200 bg-white/80">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <p class="text-center text-xs text-slate-400">
                &copy; {{ date('Y') }} PNG University of Technology · School of Mathematics, Computing &amp; Science. All rights reserved.
            </p>
        </div>
    </footer>

    @push('scripts')
    <script>
        // Mobile menu toggle
        (function () {
            const btn = document.getElementById('mobile-menu-toggle');
            const menu = document.getElementById('mobile-menu');
            if (!btn || !menu) return;
            btn.addEventListener('click', () => menu.classList.toggle('hidden'));
        })();
    </script>
    @endpush

</body>
</html>
