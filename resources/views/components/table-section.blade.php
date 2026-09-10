@props([
    'title',
    'description' => null,
    'count' => null,
    'open' => true,
    'hideTitle' => false,
])

<details class="table-section" @if ($open) open @endif>
    <summary class="flex cursor-pointer list-none items-center justify-between gap-4 border-b border-slate-200 px-5 py-4 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-brand-500">
        <span class="min-w-0">
            <span class="block text-sm font-semibold text-slate-900 @if ($hideTitle) sr-only @endif">{{ $title }}</span>
            @if ($description)
                <span class="mt-0.5 block text-xs text-slate-500 @if ($hideTitle) sr-only @endif">{{ $description }}</span>
            @endif
        </span>
        <span class="flex shrink-0 items-center gap-2 text-xs font-medium text-slate-500">
            @if ($count !== null)<span>{{ $count }}</span>@endif
            <i data-lucide="chevron-down" class="table-section-chevron h-4 w-4"></i>
        </span>
    </summary>
    {{ $slot }}
</details>
