<?php

namespace App\Policies;

use App\Models\PurchaseReceipt;
use App\Models\User;

class PurchaseReceiptPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, PurchaseReceipt $receipt): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'procurement_officer', 'purchasing_officer', 'inventory_officer', 'Administrator', 'Purchasing Officer', 'Inventory Officer'])
            || $user->can('purchase-receipts.create');
    }
}
