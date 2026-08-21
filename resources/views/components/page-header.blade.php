@php
    /*
    |-------------------------------------------------------------
    | Page Header — consistent heading above module pages
    |-------------------------------------------------------------
    | Usage:
    |   <x-page-header title="GE Orders"
    |       description="Manage General Expenditure orders and approval requests.">
    |       <x-slot name="actions">
    |           <a href="{{ route('ge-orders.create') }}" class="btn btn-primary">…</a>
    |       </x-slot>
    |   </x-page-header>
    */
    $title       = $title ?? '';
    $description = $description ?? '';
    $breadcrumbs = $breadcrumbs ?? [];
@endphp

<div class="mb-6">
    @if (! empty($breadcrumbs))
        <nav class="mb-2 flex items-center gap-1.5 text-sm text-slate-500" aria-label="Breadcrumb">
            @foreach ($breadcrumbs as $i => $crumb)
                @if ($i > 0)
                    <i data-lucide="chevron-right" class="h-3.5 w-3.5 text-slate-300"></i>
                @endif
                @if (is_array($crumb) && isset($crumb['url']))
                    <a href="{{ $crumb['url'] }}" class="hover:text-brand-600">{{ $crumb['label'] }}</a>
                @else
                    <span class="font-medium text-slate-700">{{ is_array($crumb) ? ($crumb['label'] ?? '') : $crumb }}</span>
                @endif
            @endforeach
        </nav>
    @endif

    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h2 class="text-xl font-bold tracking-tight text-slate-900">{{ $title }}</h2>
            @if ($description)
                <p class="mt-1 text-sm text-slate-500">{{ $description }}</p>
            @endif
        </div>
        @if (isset($actions))
            <div class="flex flex-wrap items-center gap-3">
                {{ $actions }}
            </div>
        @endif
    </div>
</div>
