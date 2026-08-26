@php
    /*
    |-------------------------------------------------------------
    | Status Badge — reusable pill-shaped status indicator
    |-------------------------------------------------------------
    | Usage:
    |   <x-status-badge status="approved" />
    |   <x-status-badge :status="$order->status" />
    |
    | Recognised statuses (case-insensitive) map to a colour.
    | Unknown statuses fall back to a neutral grey badge.
    */
    $status = is_string($status) ? strtolower(trim($status)) : '';
    $label  = $label ?? ucwords(str_replace(['_', '-'], ' ', $status));

    $styles = [
        // GE orders
        'draft'             => 'bg-slate-100 text-slate-600 ring-slate-200',
        'pending approval'  => 'bg-amber-50 text-amber-700 ring-amber-200',
        'pending'           => 'bg-amber-50 text-amber-700 ring-amber-200',
        'approved'          => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'rejected'          => 'bg-rose-50 text-rose-700 ring-rose-200',
        'cancelled'         => 'bg-rose-50 text-rose-700 ring-rose-200',
        // Purchases / receipts
        'ordered'           => 'bg-sky-50 text-sky-700 ring-sky-200',
        'partially received'=> 'bg-violet-50 text-violet-700 ring-violet-200',
        'fully received'    => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'received'          => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'backorder'         => 'bg-amber-50 text-amber-700 ring-amber-200',
        'complete'          => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'completed'         => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        // Inventory
        'in stock'          => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'low stock'         => 'bg-amber-50 text-amber-700 ring-amber-200',
        'out of stock'      => 'bg-rose-50 text-rose-700 ring-rose-200',
        'normal stock'      => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        // GE order types
        'stock'             => 'bg-brand-50 text-brand-700 ring-brand-200',
        'non-stock'         => 'bg-slate-100 text-slate-600 ring-slate-200',
        // Inventory movements
        'stock received'    => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'stock issue'       => 'bg-sky-50 text-sky-700 ring-sky-200',
        'stock adjustment in' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'stock adjustment out'=> 'bg-rose-50 text-rose-700 ring-rose-200',
        // Alerts
        'info'              => 'bg-sky-50 text-sky-700 ring-sky-200',
        'warning'           => 'bg-amber-50 text-amber-700 ring-amber-200',
        'error'             => 'bg-rose-50 text-rose-700 ring-rose-200',
        'success'           => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    ];
    $cls = $styles[$status] ?? 'bg-slate-100 text-slate-600 ring-slate-200';
@endphp

<span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $cls }}">
    {{ $label }}
</span>
