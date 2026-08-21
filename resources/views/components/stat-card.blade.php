@php
    /*
    |-------------------------------------------------------------
    | Stat Card — reusable dashboard statistic tile
    |-------------------------------------------------------------
    | Usage:
    |   <x-stat-card
    |       title="Total GE Orders"
    |       :value="$totalOrders"
    |       icon="file-text"
    |       icon-color="brand"
    |       :change="$ordersChange"
    |       change-direction="up"
    |       hint="vs last month" />
    |
    | Color ramps supported: brand, emerald, amber, rose, sky, violet.
    */
    $value = $value ?? '—';
    $icon  = $icon  ?? 'layout-dashboard';
    $iconColor = $iconColor ?? 'brand';
    $change = $change ?? null;
    $changeDirection = $changeDirection ?? 'up';  // up | down | flat
    $hint = $hint ?? null;

    $colorMap = [
        'brand'   => ['bg' => 'bg-brand-50',   'text' => 'text-brand-600'],
        'emerald' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600'],
        'amber'   => ['bg' => 'bg-amber-50',   'text' => 'text-amber-600'],
        'rose'    => ['bg' => 'bg-rose-50',    'text' => 'text-rose-600'],
        'sky'     => ['bg' => 'bg-sky-50',     'text' => 'text-sky-600'],
        'violet'  => ['bg' => 'bg-violet-50',  'text' => 'text-violet-600'],
    ];
    $c = $colorMap[$iconColor] ?? $colorMap['brand'];

    $arrowIcon = $changeDirection === 'down' ? 'trending-down' : ($changeDirection === 'flat' ? 'minus' : 'trending-up');
    $changeClasses = $changeDirection === 'down'
        ? 'bg-rose-50 text-rose-600'
        : ($changeDirection === 'flat' ? 'bg-slate-100 text-slate-600' : 'bg-emerald-50 text-emerald-600');
@endphp

<div class="card card-hover animate-fade-in p-5">
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <p class="truncate text-sm font-medium text-slate-500">{{ $title }}</p>
            <p class="mt-2 text-2xl font-bold tracking-tight text-slate-900">{{ $value }}</p>
        </div>
        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg {{ $c['bg'] }} {{ $c['text'] }}">
            <x-dashboard-icon name="{{ $icon }}" class="h-5 w-5" />
        </span>
    </div>

    @if ($change !== null || $hint)
        <div class="mt-4 flex items-center gap-2 text-xs">
            @if ($change !== null)
                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 font-semibold {{ $changeClasses }}">
                    <x-dashboard-icon name="{{ $arrowIcon }}" class="h-3 w-3" />
                    {{ $change }}
                </span>
            @endif
            @if ($hint)
                <span class="text-slate-400">{{ $hint }}</span>
            @endif
        </div>
    @endif
</div>
