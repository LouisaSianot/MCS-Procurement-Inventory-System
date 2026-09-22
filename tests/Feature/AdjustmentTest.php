<?php

use App\Models\Adjustment;
use App\Models\Branch;
use App\Models\InventoryMovement;
use App\Models\Item;
use App\Models\ItemBranch;
use App\Models\Supplier;
use App\Models\User;
use Spatie\Permission\Models\Role;

it('uses the shared stock service for adjustment in and out', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::firstOrCreate(['name' => 'Inventory Officer', 'guard_name' => 'web']));
    $branch = Branch::firstOrCreate(['id' => 201], ['name' => 'Main Campus']);
    $supplier = Supplier::create(['name' => 'Adjustment Test Supplier']);
    $item = Item::create(['description' => 'Adjustment Test Item', 'uom' => 'unit', 'category' => 'Consumable', 'sub_category' => 'General', 'supplier_id' => $supplier->id]);
    $stock = ItemBranch::create(['item_id' => $item->id, 'branch_id' => $branch->id, 'branch' => $branch->name, 'uom' => 'unit', 'current_stock' => 10, 'unit_cost' => 5]);

    $this->actingAs($user)->post(route('adjustments.store'), [
        'adjustment_type' => 'Adjust-IN', 'branch_id' => $branch->id, 'item_id' => $item->id,
        'quantity' => 2, 'uom' => 'unit', 'date' => '2026-09-11', 'purpose' => 'Count correction',
    ])->assertRedirect(route('adjustments.index'));

    expect((float) $stock->fresh()->current_stock)->toBe(12.0);
    $this->assertDatabaseHas('adjustments', ['adjustment_type' => 'Adjust-IN']);
    $this->assertDatabaseHas('inventory_movements', ['type' => InventoryMovement::TYPE_ADJUST_IN]);
});
