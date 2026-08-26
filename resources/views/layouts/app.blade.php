<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>
        @yield('title', config('app.name', 'MCS Purchasing & Inventory System'))
    </title>

    {{-- Inter font --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-full bg-slate-100 text-slate-800 antialiased">

    <div class="flex min-h-full">

        {{-- Sidebar --}}
        <x-sidebar :user="auth()->user()" />

        {{-- Main application area --}}
        <div class="flex min-h-full w-full flex-col lg:pl-64">

            {{-- Top navigation --}}
            <x-top-navigation :title="$title ?? 'Dashboard'" />

            {{-- Main content --}}
            <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8">

                {{-- Flash messages --}}
                @foreach (['success', 'error', 'warning', 'info'] as $type)

                @if (session()->has($type))

                <div
                    data-auto-dismiss
                    class="mb-4 flex items-center gap-3 rounded-lg border px-4 py-3 text-sm shadow-sm
                            @if($type === 'success')
                                border-emerald-200 bg-emerald-50 text-emerald-800
                            @elseif($type === 'error')
                                border-rose-200 bg-rose-50 text-rose-800
                            @elseif($type === 'warning')
                                border-amber-200 bg-amber-50 text-amber-800
                            @else
                                border-sky-200 bg-sky-50 text-sky-800
                            @endif">
                    <span class="font-medium">
                        {{ session($type) }}
                    </span>
                </div>

                @endif

                @endforeach

                {{-- Page content --}}
                {{ $slot }}

            </main>

        </div>

    </div>

    @stack('scripts')

</body>

</html>