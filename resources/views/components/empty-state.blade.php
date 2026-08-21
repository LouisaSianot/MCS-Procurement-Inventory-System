@php
    /*
    |-------------------------------------------------------------
    | Empty State — friendly placeholder shown when a list is empty
    |-------------------------------------------------------------
    | Usage:
    |   <x-empty-state icon="file-text"
    |       title="No GE Orders yet"
    |       message="Create your first General Expenditure order to get started." >
    |       <x-slot name="actions">
    |           <a href="{{ route('ge-orders.create') }}" class="btn btn-primary">Create GE Order</a>
    |       </x-slot>
    |   </x-empty-state>
    */
    $icon    = $icon ?? 'inbox';
    $title   = $title ?? 'Nothing here yet';
    $message = $message ?? '';
@endphp

<div class="flex flex-col items-center justify-center px-6 py-16 text-center">
    <span class="flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 text-slate-400">
        <i data-lucide="{{ $icon }}" class="h-7 w-7"></i>
    </span>
    <h3 class="mt-4 text-base font-semibold text-slate-900">{{ $title }}</h3>
    @if ($message)
        <p class="mt-1.5 max-w-sm text-sm text-slate-500">{{ $message }}</p>
    @endif
    @if (isset($actions))
        <div class="mt-5 flex flex-wrap items-center justify-center gap-3">
            {{ $actions }}
        </div>
    @endif
</div>
