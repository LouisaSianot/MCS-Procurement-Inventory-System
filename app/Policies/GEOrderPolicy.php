<?php

namespace App\Policies;

use App\Models\GEOrder;
use App\Models\User;

class GEOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, GEOrder $order): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return in_array($user->role ?? null, ['admin', 'procurement_officer', 'purchasing_officer'], true)
            || $user->hasRole('procurement_officer')
            || $user->can('ge-orders.create');
    }

    public function update(User $user, GEOrder $order): bool
    {
        if ($order->status !== GEOrder::STATUS_DRAFT) {
            return false;
        }

        return $order->user_id === $user->id
            || $user->can('ge-orders.update')
            || in_array($user->role ?? null, ['admin', 'procurement_officer'], true);
    }

    public function delete(User $user, GEOrder $order): bool
    {
        if (! in_array($order->status, [GEOrder::STATUS_DRAFT, GEOrder::STATUS_REJECTED])) {
            return false;
        }

        return $order->user_id === $user->id
            || $user->can('ge-orders.delete')
            || in_array($user->role ?? null, ['admin', 'procurement_officer'], true);
    }

    public function submit(User $user, GEOrder $order): bool
    {
        if ($order->status !== GEOrder::STATUS_DRAFT) {
            return false;
        }

        return $order->user_id === $user->id
            || $user->can('ge-orders.submit')
            || in_array($user->role ?? null, ['admin', 'procurement_officer', 'purchasing_officer'], true);
    }

    public function approve(User $user, GEOrder $order): bool
    {
        if (! $order->isPendingApproval()) {
            return false;
        }

        return $user->can('ge-orders.approve')
            || in_array($user->role ?? null, ['admin', 'head_of_school', 'approver'], true);
    }

    public function cancel(User $user, GEOrder $order): bool
    {
        if (in_array($order->status, [GEOrder::STATUS_CANCELLED, GEOrder::STATUS_APPROVED])) {
            return false;
        }

        return $order->user_id === $user->id
            || $user->can('ge-orders.cancel')
            || in_array($user->role ?? null, ['admin', 'procurement_officer'], true);
    }
}
