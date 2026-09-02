<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'MCS Purchasing & Inventory System') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="auth-page font-sans antialiased">
    <main class="auth-shell">
        <div class="auth-orb auth-orb-top" aria-hidden="true"></div>
        <div class="auth-orb auth-orb-bottom" aria-hidden="true"></div>
        <section class="auth-content" aria-label="Account access">
            <a href="{{ url('/') }}" class="auth-mark" aria-label="MCS Purchasing & Inventory System home">
                <i data-lucide="shopping-cart" class="h-9 w-9" aria-hidden="true"></i>
            </a>
            {{ $slot }}
        </section>
    </main>
</body>

</html>
