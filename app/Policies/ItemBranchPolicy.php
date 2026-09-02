<?php

namespace App\Policies;

use App\Models\ItemBranch;
use App\Models\User;

class ItemBranchPolicy
{
    /** Inventory is read-only; authenticated EndUsers retain view-only access. */
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ItemBranch $itemBranch): bool
    {
        return true;
    }
}
