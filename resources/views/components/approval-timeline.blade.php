@php
    /*
    |-------------------------------------------------------------
    | Approval Timeline — visual status lifecycle for a GE Order
    |-------------------------------------------------------------
    | Pass the GE Order model. Steps are derived from status/dates.
    |
    | Usage:
    |   <x-approval-timeline :order="$order" />
    */
    $order = $order ?? null;
    if (! $order) { return; }

    $status   = is_string($order->status) ? strtolower($order->status) : 'draft';
    $approval = is_string($order->approval_status ?? $order->approval ?? '') ? strtolower($order->approval_status ?? $order->approval ?? '') : '';

    // Map raw status/approval onto ordered lifecycle steps.
    $steps = [
        ['key' => 'created',  'label' => 'Created',          'icon' => 'file-plus'],
        ['key' => 'submitted','label' => 'Submitted',        'icon' => 'send'],
        ['key' => 'review',   'label' => 'Pending Approval', 'icon' => 'clock'],
        ['key' => 'decision', 'label' => 'Approved / Rejected','icon' => 'check-circle'],
        ['key' => 'procurement','label' => 'Procurement',    'icon' => 'shopping-cart'],
    ];

    // Determine the furthest reached step.
    $currentStep = 0; // created
    if (! empty($order->submitted_at) || in_array($status, ['pending','approved','rejected','cancelled']) || in_array($approval, ['pending approval','approved','rejected'])) {
        $currentStep = 2; // pending approval
    }
    if (in_array($approval, ['approved']) || in_array($status, ['approved'])) {
        $currentStep = 4; // procurement
    }
    if (in_array($approval, ['rejected']) || in_array($status, ['rejected'])) {
        $currentStep = 3; // decision (rejected)
    }
    if (in_array($status, ['cancelled'])) {
        $currentStep = 0;
    }

    $isRejected = in_array($approval, ['rejected']) || in_array($status, ['rejected']);
    $isCancelled = $status === 'cancelled';
@endphp

<div class="card animate-fade-in p-5">
    <h3 class="text-base font-semibold text-slate-900">Status Timeline</h3>
    <p class="mt-0.5 text-sm text-slate-500">Approval &amp; procurement workflow</p>

    @if ($isCancelled)
        <div class="mt-5 flex items-center gap-3 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
            <i data-lucide="ban" class="h-5 w-5 text-slate-400"></i>
            <span>This order was cancelled and is no longer in the approval workflow.</span>
        </div>
    @else
        <ol class="mt-5 space-y-0">
            @foreach ($steps as $i => $step)
                @php
                    $reached = $i <= $currentStep && ! $isRejected;
                    $active  = $i === $currentStep;
                    $rejectedHere = $isRejected && $i === 3;
                    $isLast = $i === count($steps) - 1;

                    $circleClasses = $rejectedHere
                        ? 'bg-rose-100 text-rose-600 ring-rose-200'
                        : ($reached
                            ? 'bg-brand-600 text-white ring-brand-600'
                            : ($active ? 'bg-amber-100 text-amber-600 ring-amber-200' : 'bg-slate-100 text-slate-400 ring-slate-200'));
                    $labelClasses = $rejectedHere
                        ? 'text-rose-700'
                        : ($reached ? 'text-slate-900' : 'text-slate-400');
                    $spineClasses = $rejectedHere
                        ? 'bg-rose-200'
                        : ($reached ? 'bg-brand-200' : 'bg-slate-200');
                @endphp
                <li class="relative flex gap-4 pb-{{ $isLast ? '0' : '6' }}">
                    @if (! $isLast)
                        <span class="absolute left-[18px] top-9 bottom-0 w-0.5 {{ $spineClasses }}"></span>
                    @endif
                    <span class="relative z-10 flex h-9 w-9 shrink-0 items-center justify-center rounded-full ring-4 ring-white {{ $circleClasses }}">
                        <i data-lucide="{{ $step['icon'] }}" class="h-4 w-4"></i>
                    </span>
                    <div class="pt-1.5">
                        <p class="text-sm font-semibold {{ $labelClasses }}">{{ $step['label'] }}</p>
                        @if ($step['key'] === 'created' && ! empty($order->created_at))
                            <p class="text-xs text-slate-400">{{ $order->created_at->format('d M Y, H:i') }}</p>
                        @elseif ($step['key'] === 'submitted' && ! empty($order->submitted_at))
                            <p class="text-xs text-slate-400">{{ $order->submitted_at->format('d M Y, H:i') }}</p>
                        @elseif ($step['key'] === 'decision' && ! empty($order->approved_at))
                            <p class="text-xs text-slate-400">{{ $order->approved_at->format('d M Y, H:i') }}</p>
                        @endif
                        @if ($rejectedHere)
                            <p class="mt-1 text-xs font-medium text-rose-600">Rejected</p>
                        @endif
                    </div>
                </li>
            @endforeach
        </ol>
    @endif
</div>
