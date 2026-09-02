<?php

namespace App\Policies;

use App\Models\PurchaseOrder;
use App\Models\User;

class PurchaseOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'procurement_officer', 'purchasing_officer', 'Administrator', 'Purchasing Officer'])
            || $user->can('purchase-orders.create');
    }

    public function update(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $purchaseOrder->isEditable()
            && ($purchaseOrder->user_id === $user->id
                || $user->hasAnyRole(['admin', 'procurement_officer', 'purchasing_officer', 'Administrator', 'Purchasing Officer'])
                || $user->can('purchase-orders.update'));
    }

    public function delete(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $purchaseOrder->status === PurchaseOrder::STATUS_DRAFT
            && ($purchaseOrder->user_id === $user->id
                || $user->hasAnyRole(['admin', 'procurement_officer', 'Administrator'])
                || $user->can('purchase-orders.delete'));
    }
}
