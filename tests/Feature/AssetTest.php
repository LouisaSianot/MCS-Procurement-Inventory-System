<?php

use App\Models\Asset;
use App\Models\Branch;
use App\Models\Item;
use App\Models\ItemBranch;
use App\Models\Supplier;
use App\Models\User;
use Spatie\Permission\Models\Role;

it('registers an asset only for an existing ItemBranch pair', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::firstOrCreate(['name' => 'Inventory Officer', 'guard_name' => 'web']));
    $branch = Branch::firstOrCreate(['id' => 201], ['name' => 'Main Campus']);
    $supplier = Supplier::create(['name' => 'Asset Test Supplier']);
    $item = Item::create(['description' => 'Asset Test Item', 'uom' => 'unit', 'category' => 'Asset', 'sub_category' => 'Computer', 'supplier_id' => $supplier->id]);
    ItemBranch::create(['item_id' => $item->id, 'branch_id' => $branch->id, 'branch' => $branch->name, 'uom' => 'unit', 'current_stock' => 1, 'unit_cost' => 100]);

    $this->actingAs($user)->post(route('assets.store'), [
        'asset' => 'Laptop', 'serial_number' => 'SN-001', 'brand' => 'Example', 'model' => 'X1',
        'date' => '2026-09-11', 'unit_cost' => 100, 'branch_id' => $branch->id, 'status' => 'new',
        'po_number' => 'GE-00001', 'item_id' => $item->id,
    ])->assertRedirect(route('assets.index'));

    $asset = Asset::firstOrFail();
    expect($asset->asset_id)->toBeBetween(3001, 3999);
});
