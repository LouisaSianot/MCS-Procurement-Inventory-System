<?php

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Item;
use App\Models\ItemBranch;
use App\Models\Supplier;
use App\Models\User;
use App\Models\InventoryMovement;
use Spatie\Permission\Models\Role;

it('decreases ItemBranch stock and records an issue movement', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::firstOrCreate(['name' => 'Inventory Officer', 'guard_name' => 'web']));
    $customer = Customer::factory()->create();
    $branch = Branch::firstOrCreate(['id' => 201], ['name' => 'Main Campus']);
    $supplier = Supplier::create(['name' => 'Issue Test Supplier']);
    $item = Item::create(['description' => 'Issue Test Item', 'uom' => 'unit', 'category' => 'Consumable', 'sub_category' => 'General', 'supplier_id' => $supplier->id]);
    $stock = ItemBranch::create(['item_id' => $item->id, 'branch_id' => $branch->id, 'branch' => $branch->name, 'uom' => 'unit', 'current_stock' => 10, 'unit_cost' => 5]);

    $this->actingAs($user)->post(route('issues.store'), [
        'branch_id' => $branch->id, 'item_id' => $item->id, 'quantity' => 3, 'uom' => 'unit',
        'customer_id' => $customer->id, 'purpose' => 'Staff allocation', 'date' => '2026-09-11',
    ])->assertRedirect(route('issues.index'));

    expect((float) $stock->fresh()->current_stock)->toBe(7.0);
    $this->assertDatabaseHas('inventory_movements', ['item_branch_id' => $stock->id, 'type' => InventoryMovement::TYPE_ISSUE, 'quantity' => 3]);
});
