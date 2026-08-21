@props(['icon' => 'activity', 'iconColor' => 'brand', 'actor' => 'System', 'action' => '', 'time' => ''])

@php
    $colorMap = [
        'brand' => ['bg' => 'bg-brand-50', 'text' => 'text-brand-600'],
        'emerald' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600'],
        'amber' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-600'],
        'sky' => ['bg' => 'bg-sky-50', 'text' => 'text-sky-600'],
        'violet' => ['bg' => 'bg-violet-50', 'text' => 'text-violet-600'],
        'rose' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-600'],
    ];

    $theme = $colorMap[$iconColor] ?? $colorMap['brand'];
@endphp

<div class="relative flex gap-3.5 pb-5 last:pb-0">
    <div class="relative z-10 flex h-10 w-10 shrink-0 items-center justify-center rounded-full {{ $theme['bg'] }} {{ $theme['text'] }} ring-4 ring-white">
        <i data-lucide="{{ $icon }}" class="h-4 w-4"></i>
    </div>

    <div class="min-w-0 pt-0.5">
        <p class="text-sm text-slate-700">
            <span class="font-semibold text-slate-900">{{ $actor }}</span>
            <span class="text-slate-600">{{ $action }}</span>
        </p>
        @if ($time)
            <p class="mt-1 text-xs text-slate-400">{{ $time }}</p>
        @endif
    </div>
</div>
